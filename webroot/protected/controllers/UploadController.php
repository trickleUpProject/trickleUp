<?php

class UploadController extends Controller
{
    const FORMAT_3A_2 = 'Format 3A-2';
    
    const AJAX_OP_FORMAT_FIX = 'FORMAT_FIX';
    
    const ERROR_TYPE_MULTI_VAL = 'multiVal';
    
    private $excelFormatHandlers;
    
    private $dbModelNameForFormat;
    
    
    public function init()
    {
        Yii::import('application.controllers.util.upload.*',true);
        
        $this->excelFormatHandlers[self::FORMAT_3A_2] = new Format3A2Handler3();
        
        $this->dbModelNameForFormat = array(
            self::FORMAT_3A_2 => "LivestockTracking"
        );
    }
    
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			
		);
	}
	

	// TODO: TEMPORARY IMPLEMENTATION: specific to LivestockTracking; needs to be generalized
	public function actionAjaxReportData() {
	    
	    $lsTrackings = LivestockTracking::model()->findAll();
	    
	    Yii::log("got LivestockTrackings ...", 'info', "");
        foreach($lsTrackings as $lsTracking) {
            Yii::log("livestock_number: " . $lsTracking->livestock_number, 'info', "");
        };
        
	    $this->layout=false;
	    header('Content-type: application/json');
	    echo CJavaScript::jsonEncode($lsTrackings);
	    Yii::app()->end();
	}
	
	
	
	protected function doMultiValFix(&$fixFailures, &$dirty, $badRow) {
	    
	    $importFormat = $badRow->import_format;
        $formatHdlr = $this->excelFormatHandlers[$importFormat];
        $formatModel = $formatHdlr->getModel();
        $methodName = "compound_handle_" . $dirty['name'];
        
        Yii::log("doMultiValFix: calling formatModel-method: " . $methodName, 'info', "");
        // 'null' here is original-table model's instance
        $parseResult = $formatModel->$methodName(null, $val);
        
        if($parseResult !== null) {
            if($parseResult['error']) {
                $fixFailures[] = $parseResult;
            } else {
                $dirty['parseResult'] = $parseResult;
            }
        }
	}
	
	protected function getDirtiesById($dirties) {
	    
	    $dirtiesById = array();
	    
	    foreach($dirties as $key => $val) {
	         
	        $keyParts = explode("-", $key);
	        $id = $keyParts[0];
	        $id = "id_" . $id; // because simple numeric key wouldn't behave as desired (hash-wise)
	        $colName = $keyParts[1];
	        Yii::log("id=" . $id . "; colName=" . $colName, 'info', "");
	         
	        $dirty = $dirties[$key];
	        
	        if(!array_key_exists($id, $dirtiesById)) {
	            $dirtiesById[$id] = array();
	        }
	        $dirtiesById[$id][] = $dirty;
	    }
	    
	    return $dirtiesById;
	}
	
	protected function doFormatFixesForRow($modelName, $id, $dirtiesForRow) {
	    
	    $result = array();
	    $fixFailures = array();
	    $result['fixFailures'] =& $fixFailures;
	    
	    $model = $modelName::model()->find(
	                    'id=:id',
	                    array(':id' => $id)
	    );
	    
	    $result['model'] =& $model;
	    $importFormat = $model->import_format;
	    
	    $rowData = $model->data;
	    $rowData = CJSON::decode($rowData, true);
	    
	    $fixedDirtiesByFieldName = array();
	    
	    // NOT associative! just array of dirty (each of which is array with keys: name, value, etc.)
	    foreach($dirtiesForRow as $dirty) {
	        
	        // for model field-validation, see: http://www.yiiframework.com/wiki/56/
	        // should use Yii's built-in validators where possible; need to configure
	        // model for its use; would need custom validator implementation for, e.g.,
	        // "YesOrNo"
	        
	        if(property_exists($model, $dirty['name'])) {
	            //TODO: add method to ExcelDocHandler: validate()
	            // have it use its FormatModel to look up the required validator
	            // and invoke it and return the result to here; or pass in refs to
	            // dirty and to fixFailures and have the Handler update them as needed
	        } 
	        else {
	            //TODO: use dirty[name] in concatenation to form method to
	            // call reflectively on relevant handler's format-model; pass in
	            // refs to the dirty and to fixFailures
	        }
	        
	        if(array_key_exists('parseResult', $dirty)) {
	            $fixedDirtiesByFieldName[$dirty['name']] = $dirty;
	        }
	        
	    }
	    
	    if(count($fixFailures) == 0) {
	        
	        //TODO: already have this (above)
	        $dbModelName = $this->dbModelNameForFormat($importFormat);
	        $dbModel = new $dbModelName();
	        
	        foreach($rowData as $field) {
	            
	            $parseResult = null;
	            if(array_key_exists($field['name'], $fixedDirtiesByFieldName)) {
	                $parseResult = $fixedDirtiesByFieldName[$field['name']];
	            }
	            
	            if($parseResult !== null) {
	                foreach($parseResult as $field) {
	                    $dbModel->setAttribute($field['name'], $field['value']);
	                }
	            } else {
	                $dbModel->setAttribute($field['name'], $field['value']);
	            }
	        }
	        
	        Yii::log("saving dbModel: " . $dbModelName, 'info', "");
	        if(!$dbModel->save()) {
	            Yii::log("couldn't save " . $dbModel . ": " . $dbModel->getErrors(), 'error', "");
	            // TODO: tell user that update for this row failed (NOT NULL col-value not provided, etc.)
	        }
	        
	        return null;
	        
	    } else {
	        Yii::log("fixFailures : " . count($fixFailures), 'error', "");
	        return $fixFailures;
	    }
	}
	
	protected function updateBadRowDataWithFixFailures($fixFailureResult) {
	    
	    $fixFailures = $fixFailureResult['fixFailures'];
	    $badRow = $fixFailureResult['model'];
	    
	    $badRowData = $badRow->data; // FIXME: needs JSON-decoding!
	    
	    //FIXME: JSON is to be model-specific; so, need to update only
	    // the relevant model's fieldDescrs
	    foreach($badRowData as $fieldDescr) {
	        if(array_key_exists($fieldDescr['name'], $fixFailures)) {
	            $fieldDescr['value'] = $fixFailures[$fieldDescr['name']];
	        }
	    }
	    
	    //TODO: re-encode as JSON; update model's unresolved_parse_errors_json field
	    
	    /*
	    if(!$badRow->save()) {
	        Yii::log("couldn't save badRow: " . $badRow->getErrors(), 'error', "");
	    }
	    */
	    
	    return $badRowData; // maybe instead access (at call-site) within $fixFailureResult?
	}
	
	protected function doFormatFixUpdate($data) {
	    
	    $dirties = $data['dirties'];
	    
	    foreach($dirties as $modelName => $dirtiesForModel) {
	        
	        // gather dirties by row (to later attempt to store whole row at once);
	        //  won't get that far if any of the fixes fails; or if, even with all
	        //  fixes succeeding, not all required fixes have been provided (by the user)
	        $dirtiesById = $this->getDirtiesById($dirtiesForModel);
	         
	        //TODO: need to return same message-format if still some fixFailures
	        $badRowDatas = array(); 
	         
	        foreach($dirtiesById as $key => $val) {
	            $dirtiesForId = $dirtiesById[$key];
	            $idParts = explode("_", $key);
	            $id = $idParts[1];
	            $result = $this->doFormatFixesForRow($modelName, $id, $dirtiesForId);
	            if($result !== null) {
	                $badRowDatas[] = $this->updateBadRowDataWithFixFailures($result);
	            }
	        }
	    }

	    header('Content-type: application/json');
	    
	    if(count($badRowDatas) > 0) {
	        $badRowsJSON = CJSON::encode($badRowDatas);
	        echo '{"result": ' . $badRowsJSON . '}';
	    } else {
	        echo '{"result": "All format-fixes successful.  Excel-doc imported successfully."}';
	    }
	    
	}
	
	public function actionAjaxReportDataUpdate() {
	     
	    Yii::log("handling AjaxReportDataUpdate", 'info', "");
	    Yii::log(print_r($_POST['data'], true), 'info', "");
	     
	    $data = CJSON::decode($_POST['data'], true);
	    Yii::log(print_r($data, true), 'info', "");

	    //TODO: maybe set up an ajaxMethodsMap and invoke reflectively	    
	    switch($data['op']) {
	        case self::AJAX_OP_FORMAT_FIX: {
	            $this->doFormatFixUpdate($data);
	            break;
	        }
	        default: {
	            Yii::log("unrecognized ajax-op: " . $op, 'error', "");
	            //TODO: return error-message
	        }
	    }
	}
	
	/*
	public function actionAjaxReportDataUpdate() {
	    
	    //TODO: generalize for all relevant formats (Excel-docs/sheets)
	    
	    Yii::log("handling AjaxReportDataUpdate", 'info', "");
	    Yii::log(print_r($_POST['data'], true), 'info', "");
	    
	    $dirties = CJSON::decode($_POST['data'], true);
	    Yii::log(print_r($dirties, true), 'info', "");
	    
	    foreach($dirties as $key => $val) {
	        
	        $keyParts = explode("-", $key);
	        $id = $keyParts[0];
	        $colName = $keyParts[1];
	        Yii::log("id=" . $id . "; colName=" . $colName, 'info', "");
	        
	        $dirty = $dirties[$key];
	        
	        //TODO: determine which Model to use and which col is to be used as its key in this case
	        $lsTracking = LivestockTracking::model()->find(
	                        'business_number=:business_number',
	                        array(':business_number' => $id)
	        );
	         
	        if($lsTracking) {
	            Yii::log(print_r($dirty['value'], true), 'info', "");

	            $lsTracking->setAttribute($colName, $dirty['value']);
	            
	            if(!$lsTracking->save()) {
	                Yii::log(print_r($lsTracking->getErrors(), true), 'error', "");
	            }
	        } else {
	            Yii::log("couldn't find LivestockingTracking model-instance with business_number: " . $id, 'error', "");
	        }
	    }
	    
	    header('Content-type: application/json');
	    echo '{"result": "Changes Saved"}';
	}
	*/

	/**
	 * 
	 */
	public function actionUploadExcel()
	{
	    
	    Yii::log("running actionUploadExcel", 'info', "");
	    
	    $model = new UploadForm;
	    
	    if(isset($_POST['UploadForm']))
	    {
	        
	        Yii::log("handling file-upload", 'info', "");
	        
	        $docRoot = getenv("DOCUMENT_ROOT");
	        
	        $model->attributes = $_POST['UploadForm'];
	        $model->file = CUploadedFile::getInstance($model, 'file');
	        
	        Yii::log('fileName: ' . $model->file->name, 'info', "");
	        
	        $fileStoredLoc = $docRoot . '/' . $model->file->name;
	        Yii::log('fileStoredLoc: ' . $fileStoredLoc, 'info', "");
	        $model->file->saveAs('/' . $fileStoredLoc);
	        
	        $fileExt = substr($fileStoredLoc, (strlen($fileStoredLoc) - 3));
	        Yii::log('fileExt: ' . $fileExt, 'info', "");
	        
	        Yii::import('application.vendors.PHPExcel',true);
	        
	        $objPHPExcel = null;
	        
	        try {
	            // 'Excel5' -- for .xls    'Excel2007' -- for '.xlsx'
	            $exlType = $fileExt == 'xls' ? 'Excel5' : 'Excel2007';
	            
	            $objReader = PHPExcel_IOFactory::createReader($exlType);
	            $objPHPExcel = $objReader->load($fileStoredLoc);
	            
	            $objWorksheet = $objPHPExcel->getActiveSheet();
	            
	            $formId = $objWorksheet->getCell('A1');
	            Yii::log("formId: " . $formId->getValue(), 'info', "");
	             
	            if($this->startsWith($formId->getValue(), self::FORMAT_3A_2, true)) {
	                
	                $importConfig = array(
	                                "import_file" => $fileStoredLoc, 
	                                "import_format" => self::FORMAT_3A_2
	                                );
	                
	                $result = &$this->excelFormatHandlers[self::FORMAT_3A_2]->import($objPHPExcel, $importConfig);
	                
	                if($result) {
	                    Yii::log("got formatErrors as result: " . print_r($result, true), 'error', "");
	                    $model->formatErrors = $result;
	                }
	                
	                
	            } else {
	                Yii::log("no ExcelFormatHandler found for [fileName]!", 'error', "");
	            }
	            
	        } catch(Exception $e) {
	            Yii::log("couldn't read Excel-doc: [fileName]: " . $e->getMessage() . ":" . $e->getTraceAsString(), 'error', "");
	        }
	        
	        $this->render('uploadExcel',array('model' => $model));
	        
	    } 
	    else 
	    {
	        $this->render('uploadExcel',array('model' => $model));
	    }

	}
	
	private function startsWith($haystack,$needle,$case=true) {
	    if($case)
	        return strpos($haystack, $needle, 0) === 0;
	    return stripos($haystack, $needle, 0) === 0;
	}
	

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
}
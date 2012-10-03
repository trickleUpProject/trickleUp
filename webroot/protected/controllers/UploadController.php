<?php

class UploadController extends Controller
{
    const FORMAT_3A_2 = 'Format 3A-2';
    
    const AJAX_OP_FORMAT_FIX = 'FORMAT_FIX';
    
    const ERROR_TYPE_MULTI_VAL = 'multiVal';
    
    //FIXME: move these two into FormatModel
    const METHOD_PREFIX_COMPOUND = 'compound_';
    const METHOD_PREFIX_COMPLEX = 'complex_';
    
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
	
	protected function doFormatFixesForRow($formatId, $modelName, $id, $dirtiesForRow) {
	    
	    $result = array();
	    $fixFailures = array();
	    $result['fixFailures'] =& $fixFailures;
	    
	    $model = $modelName::model()->find(
	                    'id=:id',
	                    array(':id' => $id)
	    );
	    
	    //FIXME: ensure model found
	    
	    $result['model'] =& $model;
	    
	    $rowData = $model->unresolved_parse_errors_json;
	    $rowData = CJSON::decode($rowData, true);
	    
	    $fixedDirtiesByFieldName = array();
	    
	    $formatHdlr = $this->excelFormatHandlers[$formatId];
	    $formatModel = $formatHdlr->getModel();
	    
	    // NOT associative! just array of dirty (each of which is array with keys: name, value, etc.)
	    foreach($dirtiesForRow as &$dirty) {
	        
	        // for model field-validation, see: http://www.yiiframework.com/wiki/56/
	        // should use Yii's built-in validators where possible; need to configure
	        // model for its use; would need custom validator implementation for, e.g.,
	        // "YesOrNo"
	        
	        if(property_exists($model, $dirty['name'])) {
	            
	            $cellDescr = $formatModel->getSimpleDescrForColName($dirty['name']);
	            if($cellDescr) {
	                $validatorMethodName = $cellDescr[FormatModel::CELL_VALIDATOR];
	                $method = new ReflectionMethod($formatModel, $validatorMethodName);
	                $parseResult = $method->invokeArgs($formatModel, array($dirty['name'], $dirty['value']));
	                if($parseResult !== null) {
	                    if(array_key_exists('error', $parseResult)) {
	                        $fixFailures[] = $parseResult;
	                    } else {
	                        $dirty['parseResult'] = $parseResult;
	                    }
	                }
	            }
	        } 
	        else {
	            

	            $method = null;
	            $cellName = $dirty['name'];
	            
	            try {
	                $method = new ReflectionMethod($formatModel, self::METHOD_PREFIX_COMPOUND . $cellName);
	            } catch(Exception $e) {
	                try {
	                    $method = new ReflectionMethod($formatModel, self::METHOD_PREFIX_COMPLEX . $cellName);
	                } catch(Exception $ex) {
	                    Yii::log("couldn't find compound or complex handler-method: " . $cellName, 'info', "");
	                }
	            }
	            
	            if($method) {
	                // 'null' here is original-table model's instance
	                $parseResult = $method->invokeArgs($formatModel, array(null, $dirty['value']));
	                 
	                if($parseResult !== null) {
	                    if(array_key_exists('failed', $parseResult) && $parseResult['failed'] === true) {
	                        $fixFailures[] = $parseResult;
	                    } else {
	                        $dirty['parseResult'] = $parseResult;
	                    }
	                }
	            }
	        }
	        
	        if(array_key_exists('parseResult', $dirty)) {
	            $fixedDirtiesByFieldName[$dirty['name']] = $dirty;
	        }
	    }
	    
	    unset($dirty); // weirdness about references to array-elements in a foreach
	    
	    if(count($fixFailures) == 0) { // can't store row until ALL errors fixed
	        
	        $rowFields = $rowData['fields'];
	        
	        foreach($rowFields as $fieldKey => $field) {
	            
	            $fixedDirty = null;
	            if(array_key_exists($field['name'], $fixedDirtiesByFieldName)) {
	                $fixedDirty = $fixedDirtiesByFieldName[$field['name']];
	            }
	            
	            Yii::log("fixedDirty " . print_r($fixedDirty, true), 'info', "");
	            
	            if($fixedDirty !== null) {
	                
	                $parseResult = $fixedDirty['parseResult'];
	                
	                if(array_key_exists('failed', $parseResult) && 
	                                ($parseResult['failed'] === false || $parseResult['failed'] === "false")) {
	                    // complex
	                    $model->setAttribute($parseResult['name'], $parseResult['value']);
	                } else {
	                    // compound
	                    foreach($parseResult as $pfield) {
	                        $model->setAttribute($pfield['name'], $pfield['value']);
	                    }
	                }
	                
	                //TODO: could also be simple
	                
	            } else {
	                $model->setAttribute($field['name'], $field['value']);
	            }
	        }
	        
	        $model->unresolved_parse_errors_json = null; // helps indicate row fully validated
	        
	        Yii::log("saving dbModel: " . $modelName, 'info', "");
	        if(!$model->save()) {
	            Yii::log("couldn't save " . $model . ": " . $model->getErrors(), 'error', "");
	            // TODO: tell user that update for this row failed (NOT NULL col-value not provided, etc.)
	        }
	        
	        return null; // be sure to check for this at call-site
	        
	    } else {
	        Yii::log("fixFailures : " . count($fixFailures), 'error', "");
	        return $result;
	    }
	}
	
	protected function updateBadRowDataWithFixFailures($fixFailureResult) {
	    
	    $fixFailures = $fixFailureResult['fixFailures'];
	    Yii::log("fixFailures: " . print_r($fixFailures, true), 'info', "");
	    
	    $badRow = $fixFailureResult['model'];
	    
	    $badRowData = $badRow->unresolved_parse_errors_json;
	    $badRowData = CJSON::decode($badRowData, true);

	    Yii::log("badRowData: " . print_r($badRowData, true), 'info', "");
	    
	    $rowFields = &$badRowData['fields'];
	    Yii::log("rowFields: " . print_r($rowFields, true), 'info', "");
	    
	    foreach($rowFields as $fieldKey => &$field) {
            if(array_key_exists($field['name'], $fixFailures)) {
                $field['value'] = $fixFailures[$field['name']];
            }
	    }
        
        unset($field);
        
        $badRowData = CJSON::encode($badRowData);
        $badRow->unresolved_parse_errors_json = $badRowData;

        if(!$badRow->save()) {
            Yii::log("couldn't update badRow's unresolved_parse_errors_json: " . $badRowData . 
                        "; " . $badRow->getErrors(), 'error', "");
        }
	    
	    return $badRowData; // maybe instead access (at call-site) within $fixFailureResult?
	}
	
	protected function doFormatFixUpdate($data) {
	    
	    $dirties = $data['dirties'];
	    $formatId = $data['formatId'];
	    $badRowDatas = array();
	    
	    foreach($dirties as $modelName => $dirtiesForModel) {
	        
	        // gather dirties by row (to later attempt to store whole row at once);
	        //  won't get that far if any of the fixes fails; or if, even with all
	        //  fixes succeeding, not all required fixes have been provided (by the user)
	        $dirtiesById = $this->getDirtiesById($dirtiesForModel);
	         
	        foreach($dirtiesById as $key => $val) {
	            $dirtiesForId = $dirtiesById[$key];
	            $idParts = explode("_", $key);
	            $id = $idParts[1];
	            $result = $this->doFormatFixesForRow($formatId, $modelName, $id, $dirtiesForId);
	            if($result !== null) {
	                if(!array_key_exists($modelName, $badRowDatas)) {
	                    $badRowDatas[$modelName] = array();
	                }
	                $badRowDatas[$modelName][] = $this->updateBadRowDataWithFixFailures($result);
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
	            Yii::log("unrecognized ajax-op: " . $data['op'], 'error', "");
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
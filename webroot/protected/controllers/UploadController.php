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
        
        //$this->excelFormatHandlers[self::FORMAT_3A_2] = new Format3A2Handler();
        $this->excelFormatHandlers[self::FORMAT_3A_2] = new Format3A2Handler2();
        
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
	
	
	
	protected function doMultiValFix($fixFailures, $dirty, $badRow) {
	    
	    $importFormat = $badRow->import_format;
        $methodName = "compound_handle_" . $dirty['name'];
        
        // 'null' here is original-table model's instance
        // TODO: have this type of method, in these cases (i.e., without $inst), return array of assoc arrays
        //  with keys: fieldName, value
        $parseResult = $this->excelFormatHandlers[$importFormat]->$methodName(null, $val);
        
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
	        $id = "id_" . $id; // because simple numeric key wouldn't behave as desired
	        $colName = $keyParts[1];
	        Yii::log("id=" . $id . "; colName=" . $colName, 'info', "");
	         
	        $dirty = $dirties[$key];
	        
	        if(array_key_exists($id, $dirtiesById)) {
	            $dirtiesById[$id][] = $dirty;
	        } else {
	            $dirtiesById[$id] = array();
	            $dirtiesById[$id][] = $dirty;
	        }
	    }
	    
	    return $dirtiesById;
	}
	
	protected function doFormatFixesForRow($id, $dirtiesForRow) {
	    
	    $fixFailures = array();
	    
	    $badRow = BadRow::model()->find(
	                    'id=:id',
	                    array(':id' => $id)
	    );
	    
	    $importFormat = $badRow->import_format;
	    $rowData = $badRow->data;
	    $rowData = CJSON::decode($rowData); // need to pass 'true' for assoc arrays?
	    
	    foreach($dirtiesForRow as $key => $val) {
	         
	        $keyParts = explode("-", $key);
	        $id = $keyParts[0];
	        $colName = $keyParts[1];
	        Yii::log("id=" . $id . "; colName=" . $colName, 'info', "");
	         
	        $dirty = $dirties[$key];
	         
	        // TODO: maybe, on client-side, arrange to store as special attr on td key for format-error-type,
	        //  e.g., multiVal; then, send that into here in the $dirty
	         
	        $errorType = $dirty['format-error-type'];
	        $errorType = ERROR_TYPE_MULTI_VAL; // TEMPORARY for testing
	         
	        switch($errorType) {
	            case ERROR_TYPE_MULTI_VAL: {
	                $this->doMultiValFix($fixFailures, $dirty, $badRow);
	                break;
	            }
	            default: {
	                Yii::log("unrecognized ERROR_TYPE: " . $errorType, 'error', "");
	            }
	        }
	    }
	    
	    if(count($fixFailures) == 0) {
	        
	        $dbModelName = $this->dbModelNameForFormat($importFormat);
	        $dbModel = new $dbModelName();
	        
	        foreach($rowData as $field) {
	            // need to sort of "merge" already-ok-fields with fixed fields
	            
	            // $dbModel->setAttribute($fieldName, $fieldValue);
	        }
	        
	        if(!$dbModel->save()) {
	            Yii::log($dbModel->getErrors(), 'error', "");
	            // TODO: tell user that update for this row failed (NOT NULL col-value not provided, etc.)
	        }
	    } else {
	        // TODO: tell user about failed fixes
	    }
	}
	
	protected function doFormatFixUpdate($data) {
	    
	    $dirties = $data['dirties'];
	    
	    // gather dirties by row (to later attempt to store whole row at once);
	    //  won't get that far if any of the fixes fails; or if, even with all fixes succeeding,
	    //  not all required fixes have been provided (by the user)
	    $dirtiesById = $this->getDirtiesById($dirties);
	    
	    foreach($dirtiesById as $key => $val) {
	        $dirtiesForId = $dirtiesById[$key];
	        $idParts = explode("_", $key);
	        $id = $idParts[1];
	        $this->doFormatFixesForRow($id, $dirtiesForId);
	    }
	    
	    // TODO: check for continuing format-errors (or other errors);
	    //   if any, then inform user: above, accumulate still-bad rows
	    //   and return them as when first attempting to parse excel-doc upload
	    
	    header('Content-type: application/json');
	    echo '{"result": "Changes Saved"}';
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
	                    Yii::log("got badRows as result: " . print_r($result, true), 'error', "");
	                    $model->badRows = $result;
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
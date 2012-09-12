<?php

class UploadController extends Controller
{
    const FORMAT_3A_2 = 'Format 3A-2';
    
    
    private $excelFormatHandlers;
    
    
    public function init()
    {
        Yii::import('application.controllers.util.upload.*',true);
        
        //$this->excelFormatHandlers[self::FORMAT_3A_2] = new Format3A2Handler();
        $this->excelFormatHandlers[self::FORMAT_3A_2] = new Format3A2Handler2();
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
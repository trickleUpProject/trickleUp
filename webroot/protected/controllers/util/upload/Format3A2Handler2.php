<?php

class Format3A2Handler2 extends ExcelFormatHandler {
    
    // DB-Model
    const MODEL_NAME = "LivestockTracking";
    
    // Model relating the given Excel-doc format to its DB-table counterpart
    private $formatModel;
    private $refFormatModel;
    
    
    public function __construct() {
        parent::__construct();
        
        Yii::log("importing Format3A2Model", 'info', "");
        Yii::import('application.controllers.util.upload.formatModels.Format3A2Model',true);
        
        Yii::log("instantiating Format3A2Model", 'info', "");
        $this->formatModel = new Format3A2Model();
        $this->refFormatModel = new ReflectionClass('Format3A2Model');
    }
    
    
    public function import($objPHPExcel) {
    
        Yii::import('application.vendors.PHPExcel',true);
    
        $objWorksheet = $objPHPExcel->getActiveSheet(); // TODO: do this for each sheet in current Excel-doc
    
        $lsIds = $this->formatModel->getCellCoordsforDBField('livestock_number');
        Yii::log("lsIds: " . print_r($lsIds, true), 'info', "");
        
        $cols = $lsIds[0];
        $rows = $lsIds[1]; // expecting only single row, simply based on knowledge of current format
        $row = $rows[0]; // the row the livestock_numbers are in
        
        //TODO: set livestock_number with value in "header-column", e.g., "Pig Nos."
        
        $row++; // the first row of this livestock_number's property-values
        
        $tmpLivestockNum = 0;
        
        // iterate through (i.e., across) the livestock-numbers ("Pig No." or other such)
        for($i = $cols[0]; $i < $cols[1]; $i++) {
            
            $inst = new LivestockTracking();
            $cell = null;
            
            // in current column, iterate through (i.e., down through) the property-values for this model-instance
            for($j = $row; ; $j++) {
                
                $field = $this->formatModel->getColDBFieldForRowNum($j);
                
                if($field == null) {
                    Yii::log("no field found for rowNum: " . $j, 'error', "");
                    break; // found end of row-sequence of properties for this $inst (or some error)
                }
                Yii::log("field: " . print_r($field, true), 'info', "");
                
                $cell = $objWorksheet->getCellByColumnAndRow($i, $j);
                if($cell == null) {
                    Yii::log("no cell found at col=" . $i . "; row=" . $j, 'error', "");
                    break;
                }
                
                if($field['name'] != "compound") {
                    
                    //TODO: handle cell's data-format issues, if any: esp. date-format
                    // - should be: yyyy-mm-dd  (possible i18n issues here!);
                    // - generally, if any format-issues, will need to accumulate them and
                    //   direct to View for having user correct them; then, re-process using
                    //   already uploaded copy of given Excel-doc (so, need to keep track of uploaded
                    //     but not yet processed Excel-docs; e.g., have error-correction form-submission
                    //     also send file-name of relevant recently uploaded file)
                    
                    $inst->setAttribute($field['name'], $cell->getValue());
                    
                } else {
                    $methodName = $field['methodName'];
                    Yii::log("calling compound method: " . $methodName, 'info', "");
                    $refMethod = $this->refFormatModel->getMethod($methodName);
                    $refMethod->invokeArgs($this->formatModel, array($inst, $cell->getValue()));
                }
            }
            
            if($cell == null) { // because "no cell found at ..." (above)
                break;
            }
            
            //TODO: temporary: needed to satisfy all NOT NULLs and uniqueness constraints
            $inst->business_number = $tmpLivestockNum;
            $inst->participant_name = 'Some Participant Name - ' . $tmpLivestockNum;
            $inst->year = 2012;
            $inst->quarter = 1;
            $inst->month = 1;
            $inst->livestock_number = $tmpLivestockNum++;
            $inst->livestock_type = 'pig'; // relates to which sheet is current
            
            Yii::log("saving inst: " . $inst->participant_name, 'info', "");
            $inst->save();
        }
    
    }
    
}
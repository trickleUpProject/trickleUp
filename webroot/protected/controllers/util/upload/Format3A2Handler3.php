<?php

class Format3A2Handler3 extends ExcelFormatHandler {
    
    
    
    
    public function &import($objPHPExcel, $importConfig) { // TODO: still need return-by-reference?
    
        $importTime = time();
    
        Yii::import('application.vendors.PHPExcel',true);
    
        $objWorksheet = $objPHPExcel->getActiveSheet(); // TODO: do this for each sheet in current Excel-doc
        
        Yii::import('application.controllers.util.upload.formatModels.Format3A2Model2',true);
        
        Yii::log("instantiating Format3A2Model2", 'info', "");
        $formatModel = new Format3A2Model2();
        $refFormatModel = new ReflectionClass('Format3A2Model2');
        
        $cellMap = $formatModel->getCellMap();
        
        $ppLivestockReport = new ParticipantLivestockReport();
        
        //TODO: need participant_id and business_id
        //  lookup (or create) participant using participant-name
        //  lookup (or create) business using business_number
        //  set the two id's here
        //  and set importTime
        
        $livestockStatuses = array();
        $reportParseFailures = array('report' => array(), 'statuses' => array());
        
        $haveStatusParseFailures = false;
        
        
        foreach($cellMap as $key => $cellDescr) {
            
            if($key !== Format3A2Model2::RANGE_LIVESTOCK_STATUS) {
                
                if(array_key_exists(Format3A2Model2::CELL_TYPE_COMPLEX, $cellDescr)) {
                    
                    $cellAddr = $cellDescr[Format3A2Model2::CELL_ADDRESS];
                    $cellVal = $objWorksheet->getCell($cellAddr);
                    
                    $cellHdlr = $cellDescr[Format3A2Model2::CELL_HANDLER];
                    $refMethod = $refFormatModel->getMethod($cellHdlr);
                    $hdlrResult = $refMethod->invokeArgs($formatModel, array($ppLivestockReport, $cellVal));
                    if($hdlrResult['failed']) {
                        $reportParseFailures['report'][] = $hdlrResult;
                    }
                }
            }
            else // handle RANGE_LIVESTOCK_STATUS
            {
                
                $colRange = $cellDescr[Format3A2Model2::COL_RANGE];
                $headerRow = $cellDescr[Format3A2Model2::HEADER_ROW];
                $rowRange = $cellDescr[Format3A2Model2::ROW_RANGE];
                $rowHdlrs = $cellDescr[Format3A2Model2::ROW_HANDLERS];
                
                $livestockStatus = new LivestockStatus();
                
                // iterate through (i.e., across) the livestock-numbers ("Pig No." or other such)
                for($i = $colRange[0]; $i < $colRange[1]; $i++) {
                
                    $statusParseFailures = array();
                    
                    $cell = $objWorksheet->getCellByColumnAndRow($i, $headerRow);
                    $livestockStatus->livestock_number = $cell->getValue();
                    
                    // in current column, iterate through (i.e., down through) the property-values for this model-instance
                    for($j = $rowRange[0]; $j < $rowRange[1] ; $j++) {
                        
                        $hdlr = $rowHdlrs['r' . $j];
                            
                        $cell = $objWorksheet->getCellByColumnAndRow($i, $j);
                        $cellVal = $cell->getValue();
                        
                        $cellHdlr = $hdlr[Format3A2Model2::CELL_HANDLER];
                        $refMethod = $refFormatModel->getMethod($cellHdlr);
                        $hdlrResult = $refMethod->invokeArgs($formatModel, array($livestockStatus, $cellVal));
                        
                        if($hdlr[Format3A2Model2::CELL_TYPE] == Format3A2Model2::CELL_TYPE_SIMPLE) {
                            $validator = $hdlr[Format3A2Model2::CELL_VALIDATOR];
                            $refMethod = $refFormatModel->getMethod($validator);
                            $validatorResult = $refMethod->invokeArgs($formatModel, array($livestockStatus, $hdlrResult['value']));
                            if($validatorResult['failed']) {
                                $statusParseFailures[] = $validatorResult;
                            }
                        } else {
                            if($hdlrResult['failed']) {
                                $statusParseFailures[] = $hdlrResult;
                            }
                        }
                    }
                    
                    if(count($statusParseFailures) > 0) $haveStatusParseFailures;
                }
                
                if(!$livestockStatus->save()) { // saving makes its PK available
                    // log "couldn't store livestockStatus"
                } else {
                    $this->setParseFailuresInLivestockStatus($statusParseFailures, $livestockStatus);
                }
                
                $livestockStatuses[] = $livestockStatus;
                $livestockStatus = new LivestockStatus();
            }
        }
        
        $this->setParseFailuresInLivestockReport($reportParseFailures, $ppLivestockReport);
        
        $this->storeLivestockReport($ppLivestockReport, $livestockStatuses);
        
        if(count($reportParseFailures) > 0 || $haveStatusParseFailures) {
            //TODO: add to reportParseFailures any fields from either the report-instance or
            //  the relevant status-instance that are required for the user to know which value
            //  is being referenced in the imported Excel-doc; then again, do we really need that?
            //  i.e., these are format-errors, not factual errors; only factual errors should require
            //  contextual details (associated id's, etc.)
            //TODO: get and return JSON-copy of reportParseFailures
        } else {
            // set fields in report-instance about when/who did import
            return null;
        }
    }
    
    public function setParseFailuresInLivestockStatus($parseFailures, $livestockStatus) {
        //TODO: create JSON-copy of parseFailures, set as unresolved_parse_errors_json
        // and put livestockStatus' PK into parseFailures (so can be looked up again on way back
        // during attempts by user to fix the failures)
    }
    
    public function setParseFailuresInLivestockReport($parseFailures, $ppLivestockReport) {
        //TODO: create JSON-copy of parseFailures, set as unresolved_parse_errors_json
        //  set $parseFailures['report']['id'] = {the report's PK}, so can be looked up
        //  on the way back from user's attempts to fix the failures
    }
    
    public function storeLivestockReport($ppLivestockReport, $livestockStatuses) {
        // save the report, get its PK; while storing each status, set its FK to the report
    }
}
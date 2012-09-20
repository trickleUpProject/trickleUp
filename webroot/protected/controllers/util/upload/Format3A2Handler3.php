<?php

class Format3A2Handler3 extends ExcelFormatHandler {
    
    
    
    
    public function &import($objPHPExcel, $importConfig) { // TODO: still need return-by-reference?
    
        $importTime = time();
    
        Yii::import('application.vendors.PHPExcel',true);
    
        // TODO: do all the following for each sheet in current Excel-doc
        $objWorksheet = $objPHPExcel->getActiveSheet();
        
        Yii::import('application.controllers.util.upload.formatModels.Format3A2Model2',true);
        Yii::log("instantiating Format3A2Model2", 'info', "");
        $formatModel = new Format3A2Model2();
        $refFormatModel = new ReflectionClass('Format3A2Model2');
        
        $cellMap = $formatModel->getCellMap();
        
        $ppLivestockReport = new ParticipantLivestockReport();
        
        $ppLivestockReport->import_time = $importTime;
        $ppLivestockReport->format_id = $importConfig['import_format'];
        $ppLivestockReport->source_file_name = $importConfig['import_file'];
        
        //TODO: derive these from 'period' cell?
        // - add to format-model the cell-address for period
        //   and use that here
        $ppLivestockReport->report_date = "2012-09-19";
        $ppLivestockReport->report_year = 2012;
        $ppLivestockReport->report_quarter = 1;
        
        //TODO: get these id's from lookups based on participant-name
        //  and business-number in their respective cells; so, add to
        //  format-model for these; maybe have user select such first,
        //  but, then, still check the excel-doc to make sure it's for
        //  the selected participant and business
        $ppLivestockReport->participant_id = 1;
        $ppLivestockReport->business_id = 1;
        
        // at least for now, getting livestockType at "sheet-level";
        // later, maybe at "animal-level" ?
        $sheetTitle = $objWorksheet->getTitle();
        $sheetTitleParts = explode('-', $sheetTitle);
        $livestockType = trim($sheetTitleParts[2]);
        $livestockType = strtolower($livestockType);
        
        $livestockStatuses = array();
        $reportParseFailures = array('report' => array(), 'statuses' => array());
        
        
        foreach($cellMap as $key => $cellDescr) {
            
            Yii::log("cellMap-key: " . $key, 'info', "");
            
            Yii::log("handling cellDescr: " . print_r($cellDescr, true), 'info', "");
            
            if($key !== Format3A2Model2::RANGE_LIVESTOCK_STATUS) {
                
                if($cellDescr[Format3A2Model2::CELL_TYPE] == Format3A2Model2::CELL_TYPE_COMPLEX) {
                    
                    $cellAddr = $cellDescr[Format3A2Model2::CELL_ADDRESS];
                    $cell = $objWorksheet->getCell($cellAddr);
                    $cellVal = $cell->getValue();
                    Yii::log("cellVal: " . print_r($cellVal, true), 'info', "");
                    
                    $cellHdlr = $cellDescr[Format3A2Model2::CELL_HANDLER];
                    $refMethod = $refFormatModel->getMethod($cellHdlr);
                    Yii::log("calling cellHdlr: " . print_r($cellHdlr, true), 'info', "");
                    
                    $hdlrResult = $refMethod->invokeArgs($formatModel, array($ppLivestockReport, $cellVal));
                    if($hdlrResult) {
                        Yii::log("parse failed: hdlrResult: " . print_r($hdlrResult, true), 'info', "");
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
                
                $livestockStatus->livestock_type = $livestockType;
                
                // iterate through (i.e., across) the livestock-numbers ("Pig No." or other such)
                for($i = $colRange[0]; $i < $colRange[1]; $i++) {
                
                    $statusParseFailures = array();
                    
                    $cell = $objWorksheet->getCellByColumnAndRow($i, $headerRow);
                    $livestockStatus->livestock_number = $cell->getValue();
                    Yii::log("handling livestock_number: " . $cell->getValue(), 'info', "");
                    
                    // in current column, iterate through (i.e., down through) the property-values for this model-instance
                    for($j = $rowRange[0]; $j < $rowRange[1] ; $j++) {
                        
                        $hdlr = $rowHdlrs['r' . $j];
                        Yii::log("using rowHdlr: " . print_r($hdlr, true), 'info', "");
                            
                        $cell = $objWorksheet->getCellByColumnAndRow($i, $j);
                        $cellVal = $cell->getValue();
                        
                        if(!$cellVal) {  // TODO: for now, assuming no cell-vals are required
                            Yii::log("skipping empty cell at: " . $i . "," . $j , 'info', "");
                            continue;
                        } else {
                            Yii::log("processing cell-value: " . $cellVal , 'info', "");
                        }
                        
                        if($hdlr[Format3A2Model2::CELL_TYPE] == Format3A2Model2::CELL_TYPE_SIMPLE) {
                            $validator = $hdlr[Format3A2Model2::CELL_VALIDATOR];
                            $refMethod = $refFormatModel->getMethod($validator);
                            
                            $cellDBColName = $hdlr[Format3A2Model2::CELL_DB_COL_NAME];
                            Yii::log("calling validator for " . $cellDBColName . ": " . print_r($validator, true), 'info', "");
                            
                            $validatorResult = $refMethod->invokeArgs($formatModel, array($cellDBColName, $cellVal));
                            if($validatorResult) {
                                Yii::log("validation failed: validatorResult: " . print_r($validatorResult, true), 'info', "");
                                $statusParseFailures[] = $validatorResult;
                            }
                        } else {
                            $cellHdlr = $hdlr[Format3A2Model2::CELL_HANDLER];
                            $refMethod = $refFormatModel->getMethod($cellHdlr);
                            Yii::log("calling cellHdlr: " . print_r($cellHdlr, true), 'info', "");
                            $hdlrResult = $refMethod->invokeArgs($formatModel, array($livestockStatus, $cellVal));
                            
                            if($hdlrResult && array_key_exists('failed', $hdlrResult)) {
                                Yii::log("parse failed: hdlrResult: " . print_r($hdlrResult, true), 'info', "");
                                $statusParseFailures[] = $hdlrResult;
                            }
                        }
                        
                    } // end row-range
                    
                    if(!$livestockStatus->save()) { // saving makes its PK available
                        Yii::log("couldn't save livestockStatus: " . $livestockStatus->getErrors(), 'error', "");
                    } else {
                        
                        Yii::log("calling setParseFailuresInLivestockStatus with statusParseFailures: " . 
                            print_r($statusParseFailures, true), 'info', "");
                        
                        if(count($statusParseFailures) > 0) {
                            $json = $this->setParseFailuresInLivestockStatus($statusParseFailures, $livestockStatus);
                            $reportParseFailures['statuses'][] = $json;
                        }
                    }
                    
                    $livestockStatuses[] = $livestockStatus;
                    $livestockStatus = new LivestockStatus();
                    
                } // end col-range
            }
        }
        
        $this->storeLivestockReport($ppLivestockReport, $livestockStatuses);
        
        $json = $this->setParseFailuresInLivestockReport($reportParseFailures, $ppLivestockReport);
        $reportParseFailures['report'] = $json;
        
        if(count($reportParseFailures['report']) > 0 || count($reportParseFailures['statuses']) > 0) {
            
            //TODO: add to reportParseFailures any fields from either the report-instance or
            //  the relevant status-instance that are required for the user to know which value
            //  is being referenced in the imported Excel-doc; then again, do we really need that?
            //  i.e., these are format-errors, not factual errors; only factual errors should require
            //  contextual details (associated id's, etc.) -- other than PK of given item
            
            $str = '{"report":' . $reportParseFailures['report'] . ', "statuses":[';
            
            $numStats = count($reportParseFailures['statuses']);
            
            if($numStats > 0) {
                for($i = 0; $i < $numStats; $i++) {
                    $statusJSON = $reportParseFailures['statuses'][$i];
                    $str = $str . $statusJSON;
                    if($i < ($numStats - 1)) $str = $str . ',';
                }
            }
            
            $str = $str . "]}";
            
            //$str = '{"report":' . $reportParseFailures['report'] . ', "statuses":' . $reportParseFailures['statuses'] . '}';
            Yii::log("returning JSON: " . $str, 'info', "");
            return $str;
            
        } else {
            Yii::log("returning null; apparently no parseFailures", 'info', "");
            return null;
        }
    }
    
    public function setParseFailuresInLivestockStatus($parseFailures, $livestockStatus) {
        $parseFailures[] = array('name' => 'id', 'value' => $livestockStatus->getPrimaryKey());
        $json = CJSON::encode($parseFailures);
        $livestockStatus->unresolved_parse_errors_json = $json;
        Yii::log("setParseFailuresInLivestockStatus: returning json: " . $json, 'error', "");
        return $json;
    }
    
    public function setParseFailuresInLivestockReport($parseFailures, $ppLivestockReport) {
        $parseFailures['report'][] = array('name' => 'id', 'value' => $ppLivestockReport->getPrimaryKey());
        $json = CJSON::encode($parseFailures['report']);
        $ppLivestockReport->unresolved_parse_errors_json = $json;
        return $json;
    }
    
    public function storeLivestockReport($ppLivestockReport, $livestockStatuses) {
        if(!$ppLivestockReport->save()) {
            Yii::log("couldn't save livestockReport: " . $ppLivestockReport->getErrors(), 'error', "");
        } else {
            $reportPK = $ppLivestockReport->getPrimaryKey();
            foreach($livestockStatuses as $status) {
                $status->participant_livestock_report_id = $reportPK;
                if(!$status->save()) {
                    Yii::log("couldn't update livestockStatus with reportPK: statusPK=" . 
                        $status->getPrimaryKey() . 
                        "reportPK=" . $reportPK . ": " . 
                        $ppLivestockReport->getErrors(), 'error', "");
                }
            }
        }
    }
}
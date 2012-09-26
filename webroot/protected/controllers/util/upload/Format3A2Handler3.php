<?php

class Format3A2Handler3 extends ExcelFormatHandler {
    
    
    public function &import($objPHPExcel, $importConfig) { // TODO: still need return-by-reference?
    
        $importTime = time();
    
        Yii::import('application.vendors.PHPExcel',true);
    
        // TODO: do all the following for each sheet in current Excel-doc
        $objWorksheet = $objPHPExcel->getActiveSheet();
        
        Yii::import('application.controllers.util.upload.formatModels.*',true);
        //Yii::import('application.controllers.util.upload.formatModels.Format3A2Model2',true);
        
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
        
        // using nested array here for 'report' to simplify front-end: it just iterates
        // over the errors-array for each table; in case of 'report', it happens to iterate only once
        $reportParseFailures = array(Format3A2Model2::MODEL_REPORT => array(array()), 
                                     Format3A2Model2::MODEL_LIVESTOCK_STATUS => array());
        
        
        foreach($cellMap as $key => $cellDescr) {
            
            Yii::log("cellMap-key: " . $key, 'info', "");
            
            Yii::log("handling cellDescr: " . print_r($cellDescr, true), 'info', "");
            
            if($key !== Format3A2Model2::RANGE_LIVESTOCK_STATUS) {
                
                if($cellDescr[FormatModel::CELL_TYPE] == FormatModel::CELL_TYPE_COMPLEX) {
                    
                    $cellAddr = $cellDescr[FormatModel::CELL_ADDRESS];
                    $cell = $objWorksheet->getCell($cellAddr);
                    $cellVal = $cell->getValue();
                    Yii::log("cellVal: " . print_r($cellVal, true), 'info', "");
                    
                    $cellHdlr = $cellDescr[FormatModel::CELL_HANDLER];
                    $refMethod = $refFormatModel->getMethod($cellHdlr);
                    Yii::log("calling cellHdlr: " . print_r($cellHdlr, true), 'info', "");
                    
                    $hdlrResult = $refMethod->invokeArgs($formatModel, array($ppLivestockReport, $cellVal));
                    if($hdlrResult) {
                        Yii::log("parse failed: hdlrResult: " . print_r($hdlrResult, true), 'info', "");
                                                                       // 'id' for each report-level item to be added below
                        $reportParseFailures[Format3A2Model2::MODEL_REPORT][0][] = array('field' => $hdlrResult);
                    }
                }
            }
            else // handle RANGE_LIVESTOCK_STATUS
            {
                
                $colRange = $cellDescr[FormatModel::COL_RANGE];
                $headerRow = $cellDescr[FormatModel::HEADER_ROW];
                $rowRange = $cellDescr[FormatModel::ROW_RANGE];
                $rowHdlrs = $cellDescr[FormatModel::ROW_HANDLERS];
                
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
                        
                        if($hdlr[FormatModel::CELL_TYPE] == FormatModel::CELL_TYPE_SIMPLE) {
                            $validator = $hdlr[FormatModel::CELL_VALIDATOR];
                            $refMethod = $refFormatModel->getMethod($validator);
                            
                            $cellDBColName = $hdlr[FormatModel::CELL_DB_COL_NAME];
                            Yii::log("calling validator for " . $cellDBColName . ": " . print_r($validator, true), 'info', "");
                            
                            $validatorResult = $refMethod->invokeArgs($formatModel, array($cellDBColName, $cellVal));
                            if($validatorResult) {
                                Yii::log("validation failed: validatorResult: " . print_r($validatorResult, true), 'info', "");
                                $statusParseFailures['field'] = $validatorResult;
                            }
                        } else {
                            $cellHdlr = $hdlr[FormatModel::CELL_HANDLER];
                            $refMethod = $refFormatModel->getMethod($cellHdlr);
                            Yii::log("calling cellHdlr: " . print_r($cellHdlr, true), 'info', "");
                            $hdlrResult = $refMethod->invokeArgs($formatModel, array($livestockStatus, $cellVal));
                            
                            if($hdlrResult && array_key_exists('failed', $hdlrResult)) {
                                Yii::log("parse failed: hdlrResult: " . print_r($hdlrResult, true), 'info', "");
                                $statusParseFailures['field'] = $hdlrResult;
                            }
                        }
                        
                    } // end row-range
                    
                    if(!$livestockStatus->save()) { // saving makes its PK available
                        Yii::log("couldn't save LivestockStatus: " . $livestockStatus->getErrors(), 'error', "");
                    } else {
                        
                        Yii::log("calling setParseFailuresInLivestockStatus with statusParseFailures: " . 
                            print_r($statusParseFailures, true), 'info', "");
                        
                        if(count($statusParseFailures) > 0) {
                            $json = $this->setParseFailuresInLivestockStatus($statusParseFailures, $livestockStatus);
                            $reportParseFailures[Format3A2Model2::MODEL_LIVESTOCK_STATUS][] = $json;
                        }
                    }
                    
                    $livestockStatuses[] = $livestockStatus;
                    $livestockStatus = new LivestockStatus();
                    
                } // end col-range
            }
        }
        
        $this->storeLivestockReport($ppLivestockReport, $livestockStatuses);
        
        $json = $this->setParseFailuresInLivestockReport($reportParseFailures, $ppLivestockReport);
        $reportParseFailures[Format3A2Model2::MODEL_REPORT][0] = $json;
        
        if(count($reportParseFailures[Format3A2Model2::MODEL_REPORT][0]) > 0 || 
                        count($reportParseFailures[Format3A2Model2::MODEL_LIVESTOCK_STATUS]) > 0) {
            
            //TODO: add to reportParseFailures any fields from either the report-instance or
            //  the relevant status-instance that are required for the user to know which value
            //  is being referenced in the imported Excel-doc; then again, do we really need that?
            //  i.e., these are format-errors, not factual errors; only factual errors should require
            //  contextual details (associated id's, etc.) -- other than PK of given item
            
            $str = '{"' . Format3A2Model2::MODEL_REPORT . '":' . $reportParseFailures[Format3A2Model2::MODEL_REPORT][0] . 
                        ', "' . Format3A2Model2::MODEL_LIVESTOCK_STATUS .'":[';
            
            $numStats = count($reportParseFailures[Format3A2Model2::MODEL_LIVESTOCK_STATUS]);
            
            if($numStats > 0) {
                for($i = 0; $i < $numStats; $i++) {
                    $statusJSON = $reportParseFailures[Format3A2Model2::MODEL_LIVESTOCK_STATUS][$i];
                    $str = $str . $statusJSON;
                    if($i < ($numStats - 1)) $str = $str . ',';
                }
            }
            
            $str = $str . "]}";
            Yii::log("returning JSON: " . $str, 'info', "");
            return $str;
            
        } else {
            Yii::log("returning null; apparently no parseFailures", 'info', "");
            return null;
        }
    }
    
    public function setParseFailuresInLivestockStatus($parseFailures, $livestockStatus) {
        $parseFailures['id'] = $livestockStatus->getPrimaryKey();
        $json = CJSON::encode($parseFailures);
        $livestockStatus->unresolved_parse_errors_json = $json;
        Yii::log("setParseFailuresInLivestockStatus: returning json: " . $json, 'error', "");
        return $json;
    }
    
    public function setParseFailuresInLivestockReport($parseFailures, $ppLivestockReport) {
        
        //$parseFailures[Format3A2Model2::MODEL_REPORT][0]['id'] = $ppLivestockReport->getPrimaryKey();
        
        $pk = $ppLivestockReport->getPrimaryKey();
        $failures = $parseFailures[Format3A2Model2::MODEL_REPORT][0];
        
        foreach($failures as $failure) {
            $failure['id'] = $pk;
        }
        
        $json = CJSON::encode($failures);
        $ppLivestockReport->unresolved_parse_errors_json = $json;
        return $json;
    }
    
    public function storeLivestockReport($ppLivestockReport, $livestockStatuses) {
        if(!$ppLivestockReport->save()) {
            Yii::log("couldn't save ParticipantLivestockReport: " . $ppLivestockReport->getErrors(), 'error', "");
        } else {
            $reportPK = $ppLivestockReport->getPrimaryKey();
            foreach($livestockStatuses as $status) {
                $status->participant_livestock_report_id = $reportPK;
                if(!$status->save()) {
                    Yii::log("couldn't update LivestockStatus with reportPK: statusPK=" . 
                        $status->getPrimaryKey() . 
                        "reportPK=" . $reportPK . ": " . 
                        $ppLivestockReport->getErrors(), 'error', "");
                }
            }
        }
    }
}
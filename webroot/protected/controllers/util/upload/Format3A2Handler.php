<?php

class Format3A2Handler extends ExcelFormatHandler {
    
    const MODEL_NAME = "LivestockTracking";
    
    private $fieldMapping = array(
        "Age in months" => "age_in_months",
        "Weight (KG)" => "weight_kg",
        "Deworming Done         (Date/ N/ N.A.)" => "deworming_done", // TODO: contains date-format?
        "Problem in Concieving (Y/ N/ N.A.)" => "problem_conceiving",
        "Concentrate during pregnancy" => "concentrate_during_pregnancy",
        "Miscarriage & reason" => array('compound', 'compound_handleMiscarriageAndReason'),
        "Date of delivery" => "delivery_date", // TODO: which date-format?
        "No. of kids born M/F " => array('compound', 'compound_handleNumKidsBornMF'),
        "Death & reason" => array('compound', 'compound_handleDeathAndReason'),
        "Sold & price" => array('compound', 'compound_handleSoldSalePrice')
    );
    
    // START:
    // special methods called typically only via reflection (below), 
    //  when there's no one-to-one relationship btw fieldName and modelField
    
    public function compound_handleMiscarriageAndReason($args /* $inst, $fieldVal */) {
        $inst = $args[0];
        $fieldVal = $args[1];
        list($miscarriage, $reason) = split("&", $fieldVal);
        Yii::log("miscarriage=". $miscarriage . "; reason=" . $reason, 'info', "");
        $miscarriage = trim($miscarriage);
        if($miscarriage != 'Y' && $miscarriage != 'N') {
            Yii::log("miscarriage value neither 'Y' nor 'N'", 'error', "");
            return;
        }
        $inst->miscarriage = $miscarriage;
        $inst->miscarriage_reason = $reason;
    }
    
    public function compound_handleNumKidsBornMF($args /* $inst, $fieldVal */) {
        $inst = $args[0];
        $fieldVal = $args[1];
        list($numKidsM, $numKidsF) = split("|", $fieldVal);
        $inst->num_kids_m = intval($numKidsM);
        $inst->num_kids_f = intval($numKidsF);
    }
    
    public function compound_handleDeathAndReason($args /* $inst, $fieldVal */) {
        $inst = $args[0];
        $fieldVal = $args[1];
        list($death, $reason) = split("&", $fieldVal);
        $death = trim($death);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        $inst->death = $death;
        $inst->reason_for_death = $reason;
    }
    
    public function compound_handleSoldSalePrice($args /* $inst, $fieldVal */) {
        $inst = $args[0];
        $fieldVal = $args[1];
        list($sold, $salePrice) = split("&", $fieldVal);
        $sold = trim($sold);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        $inst->sold = $sold;
        $inst->sale_price = $salePrice; // float type! should instead be, e.g., DECIMAL(8,2)
        /*
        DECIMAL would suit your needs better -- from [2]: "The DECIMAL and 
        NUMERIC types [...] are used to store values for which it is important 
        to preserve exact precision, for example with monetary data." 
        */
    }
    
    // END: special compound methods
    
    
    
    
    public function import($objPHPExcel) {
        
        Yii::import('application.vendors.PHPExcel',true);
        
        $refClass = new ReflectionClass(self::MODEL_NAME);
        $thisRefClass = new ReflectionClass($this);
        
        $objWorksheet = $objPHPExcel->getActiveSheet();
        
        //TODO: handle the other sheet(s), if any
        
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
        $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
        
        $instances = array();
        Yii::log("rc: instances-count: '". count($instances) . "'", 'info', "");
        
        $tmpLivestockNum = 1;
        
        $tmpItrCount = 0;
        
        for ($row = 4; $row < ($highestRow-1); $row++) {
             
            Yii::log("handling row " . $row, 'info', "");
            $inst = null;
            $fieldName = null;
            $modelField = null;
            $propToSet = null;
            $compoundFieldHandlerName = null;
            
            Yii::log("rc: row: '". $row . "'", 'info', "");
            Yii::log("rc: instances-count: '". count($instances) . "'", 'info', "");
            
            if($row == 4) {
                for ($col = 1; $col < $highestColumnIndex; $col++) {
                    $bizNum = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    $inst = new LivestockTracking;
                    $inst->setAttribute('business_number', $bizNum);
                    $instances[] = $inst;
                    Yii::log("rc: instances-count: '". count($instances) . "'", 'info', "");
                }
            } else {
                
                for ($col = 0; $col < ($highestColumnIndex-1); $col++) {
                    
                    Yii::log("rc: col: '". $col . "'", 'info', "");
                    
                    if($col == 0) {
                        $fieldName = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                        Yii::log("fieldName: '". $fieldName . "'", 'info', "");
                        $modelField = $this->fieldMapping[$fieldName];
                        Yii::log("modelField: '". $modelField . "'", 'info', "");
                        if($modelField == null) {
                            // TODO: maybe try to regex-capture in this, as a second try ?
                            Yii::log("unrecognized term: '". $fieldName . "'", 'error', "");
                            break;
                        } else {
                            if(is_array($modelField)) {
                                $compoundFieldHandlerName = $modelField[1];
                            } else {
                                //$propToSet = $refClass->getProperty($modelField);
                            }
                        }
                    } else {
                        Yii::log("rc: seeking instance: '". $col . "'", 'info', "");
                        $inst = $instances[$col];
                        $fieldVal = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                        
                        if($compoundFieldHandlerName != null) {
                            Yii::log("setting fieldVal' . $fieldVal . ' using: '". $compoundFieldHandlerName . "'", 'info', "");
                            $reflectionMethod = new ReflectionMethod('Format3A2Handler', $compoundFieldHandlerName);
                            $reflectionMethod->invokeArgs($this, array($inst, $fieldVal));
                        } else {
                            Yii::log("setting fieldVal' . $fieldVal . ' on modelField: '". $modelField . "'", 'info', "");
                            $inst->setAttribute($modelField, $fieldVal);
                        }
                    }
                    
                    if($col > 0 && $inst != null) {
                        //TODO: remove these: temporary, because NOT NULL
                        $inst->participant_name='Some Participant Name - ' . $tmpLivestockNum;
                        $inst->year = 2012;
                        $inst->quarter = 1;
                        $inst->month = 1;
                        $inst->livestock_number = $tmpLivestockNum++;
                        $inst->livestock_type = 'pig'; // relates to which sheet is current
                        
                        Yii::log("rc: saving inst with bizNum: " . $inst->getAttribute('business_number') . 
                            " row=" . $row . "; col=" . $col, 'info', "");
                        $inst->save();
                        
                        $fieldName = null;
                        //$modelField = null;
                        $propToSet = null;
                        $compoundFieldHandlerName = null;
                        $inst = null;
                    }
                }
                
                //if(++$tmpItrCount == 5) break;
            }
        }
    }
}
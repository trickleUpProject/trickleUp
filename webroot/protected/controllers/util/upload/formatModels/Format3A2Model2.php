<?php

class Format3A2Model2 extends FormatModel {
    
    // associated DB-Models
    const MODEL_REPORT = "ParticipantLivestockReport";
    const MODEL_LIVESTOCK_STATUS = "LivestockStatus";
    
    // special excel-doc areas, if any
    const RANGE_LIVESTOCK_STATUS = 'livestockStatusRange';
    
    
    private $cellMap;
    private $simpleDescrForColName;
    

    public function __construct() {
        
        $this->simpleDescrForColName = array(
            "age_months" => array(
                                     FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_SIMPLE,
                                     FormatModel::CELL_DB_COL_NAME => 'age_months',
                                     FormatModel::CELL_HANDLER => 'getSimpleCellValue',
                                     FormatModel::CELL_VALIDATOR => 'getInt'
                                 )
            //TODO: add the other "simples" here, and reference them below where needed
        );
        
        $this->cellMap = array(
            'shed_condition' => array(
                            FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPLEX, 
                            FormatModel::CELL_ADDRESS => 'E15', 
                            FormatModel::CELL_HANDLER => 'complex_ShedCondition'
                            ),
            'maintenance_cleanliness' => array(
                            FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPLEX, 
                            FormatModel::CELL_ADDRESS => 'I15',
                            FormatModel::CELL_HANDLER => 'complex_MaintenanceCleanliness'
                            ),
            'KMn04_application' => array(
                            FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPLEX, 
                            FormatModel::CELL_ADDRESS => 'A16',
                            FormatModel::CELL_HANDLER => 'complex_KMn04Application'
                            ),
            'separation_if_pregnant' => array(
                            FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPLEX, 
                            FormatModel::CELL_ADDRESS => 'A15',
                            FormatModel::CELL_HANDLER => 'complex_SeparationIfPregnant'
                            ),
                        
            //////////////////////////////////////////////////
        
            self::RANGE_LIVESTOCK_STATUS => array(
                FormatModel::COL_RANGE => array(1,12),
                FormatModel::HEADER_ROW => 4,
                FormatModel::ROW_RANGE => array(5, 14),
                FormatModel::ROW_HANDLERS => array(
                    'r5' => $this->simpleDescrForColName['age_months'],
                    'r6' => array( // weight_kg
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_SIMPLE,
                                    FormatModel::CELL_DB_COL_NAME => 'weight_kg',
                                    FormatModel::CELL_HANDLER => 'getSimpleCellValue',
                                    FormatModel::CELL_VALIDATOR => 'getDecimal'
                    ),
                    'r7' => array( // deworm
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_SIMPLE,
                                    FormatModel::CELL_DB_COL_NAME => 'deworm',
                                    FormatModel::CELL_HANDLER => 'getSimpleCellValue',
                                    FormatModel::CELL_VALIDATOR => 'getDate'
                    ),
                    'r8' => array( // problem_conceiving
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_SIMPLE,
                                    FormatModel::CELL_DB_COL_NAME => 'problem_conceiving',
                                    FormatModel::CELL_HANDLER => 'getSimpleCellValue',
                                    FormatModel::CELL_VALIDATOR => 'getYesNo'
                    ),
                    'r9' => array( // concentrate_during_pregnancy
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_SIMPLE,
                                    FormatModel::CELL_DB_COL_NAME => 'concentrate_during_pregnancy',
                                    FormatModel::CELL_HANDLER => 'getSimpleCellValue',
                                    FormatModel::CELL_VALIDATOR => 'getYesNo'
                    ),
                    'r10' => array( // miscarriage_date, miscarriage_reason
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPOUND,
                                    FormatModel::CELL_HANDLER => 'compound_MisCarriageDateAndReason',
                                    FormatModel::CELL_VALIDATOR => self::CELL_VALIDATOR_IN_HANDLER
                    ),
                    'r11' => array( // delivery_date
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_SIMPLE,
                                    FormatModel::CELL_DB_COL_NAME => 'delivery_date',
                                    FormatModel::CELL_HANDLER => 'getSimpleCellValue',
                                    FormatModel::CELL_VALIDATOR => 'getDate'
                    ),
                    'r12' => array( // num_kids_born_m, num_kids_born_f
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPOUND,
                                    FormatModel::CELL_HANDLER => 'compound_NumKidsBornMF',
                                    FormatModel::CELL_VALIDATOR => FormatModel::CELL_VALIDATOR_IN_HANDLER
                    ),
                    'r13' => array( // death_date, death_reason
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPOUND,
                                    FormatModel::CELL_HANDLER => 'compound_DeathDateAndReason',
                                    FormatModel::CELL_VALIDATOR => FormatModel::CELL_VALIDATOR_IN_HANDLER
                    ),
                    'r14' => array( // sale_date, sale_price
                                    FormatModel::CELL_TYPE => FormatModel::CELL_TYPE_COMPOUND,
                                    FormatModel::CELL_HANDLER => 'compound_SaleDateAndPrice',
                                    FormatModel::CELL_VALIDATOR => FormatModel::CELL_VALIDATOR_IN_HANDLER
                    )

                )
            ),
                        
            // other?
        );
    }
    
    public function getSimpleDescrForColName($colName) {
        if(array_key_exists($colName, $this->simpleDescrForColName)) {
            return $this->simpleDescrForColName[$colName];
        }
        return null;
    }
    
    public function getCellMap() {
        return $this->cellMap;
    }
    
    
    
    public function getInt($dbColName, $val) {
        if(is_numeric($val)) return null;
        return array('name' => $dbColName, 'value' => $val);
    }
    
    public function getDecimal($dbColName, $val) {
        //TODO: implement this
        return null;
    }
    
    public function getDate($dbColName, $val) {
        //TODO: implement this
        return null;
    }
    
    public function getYesNo($dbColName, $val) {
        if($val == 'Y' || $val == 'N') {
            return null;
        } else {
            return array('failed' => true, 'name' => $dbColName,'value' => $val);
        }
    }
    
    
    
    public function complex_ShedCondition($report, $str) {
        if(!$str) return null;
        $result = array('failed' => true, 'name' => 'ShedCondition', 'value' => $str);
        try {
            // "Shed condition: ______ / 5"  // do I seriously have to translate a count of underscores into a number?
            $parts = explode(':', $str);
            if(!$parts || count($parts) < 2) return $result;
            $part = $parts[1];
            $parts = explode('/', $part);
            if(!$parts || count($parts) < 2) return $result;
            $val = trim($parts[0]);
            
            if($report) {
                $report->shed_condition = $val;
                return null;
            } else {
                $result['failed'] = false;
                $result['value'] = $val;
                return $result;
            }
        } 
        catch(Exception $e) {
            Yii::log("Cell Format-Error: couldn't parse ShedCondition: " . $str, 'error', "");
            return $result;
        }
    }
    
    public function complex_MaintenanceCleanliness($report, $str) {
        if(!$str) return null;
        $result = array('failed' => true, 'name' => 'MaintenanceCleanliness', 'value' => $str);
        try {
            // "Maintenance & Cleanliness (Y/N):"
            $parts = explode(':', $str);
            if(!$parts || count($parts) < 2) return $errResult;
            $val = trim($parts[1]);
            
            if($report) {
                $report->maintenance_cleanliness = $val;
                return null;
            } else {
                $result['failed'] = false;
                $result['value'] = $val;
                return $result;
            }
        } 
        catch(Exception $e) {
            Yii::log("Cell Format-Error: couldn't parse MaintenanceCleanliness: " . $str, 'error', "");
            return $result;
        }
    }
    
    public function complex_KMn04Application($report, $str) {
        if(!$str) return null;
        $result = array('failed' => true, 'name' => 'KMn04Application', 'value' => $str);
        try {
            // "KMnO4 appication before and after delivery (Y/N):"
            $parts = explode(':', $str);
            if(!$parts || count($parts) < 2) return $result;
            $val = trim($parts[1]);
            
            if($report) {
                $report->KMn04_application = $val;
                return null;
            } else {
                $result['failed'] = false;
                $result['value'] = $val;
                return $result;
            }
        } 
        catch(Exception $e) {
            Yii::log("Cell Format-Error: couldn't parse KMn04Application: " . $str, 'error', "");
            return $result;
        }
    }
    
    public function complex_SeparationIfPregnant($report, $str) {
        if(!$str) return null;
        // here, we use method-name-related value for 'name', to enable finding this method again, if necessary
        $result = array('failed' => true, 'name' => 'SeparationIfPregnant', 'value' => $str);
        try {
            // Separation of pregnant goat (Y/N / N.A.): N
            $parts = explode(':', $str);
            if(!$parts || count($parts) < 2) return $result;
            $val = trim($parts[1]);
            
            if($report) {
                $report->separation_if_pregnant = $val;
                return null;
            } else {
                $result['failed'] = "false"; // seems to end up empty if not quoted (?)
                $result['name'] = 'separation_if_pregnant'; // here, we prepare for setting this single field on the model
                $result['value'] = $val;
                return $result;
            }
        } 
        catch(Exception $e) {
            Yii::log("Cell Format-Error: couldn't parse SeparationIfPregnant: " . $str, 'error', "");
            return $result;
        }
    }
    
    
    
    public function compound_MisCarriageDateAndReason($status, $fieldVal) {
        if(!$fieldVal) return null;
        $parts = explode("|", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse MisCarriageDateAndReason: " . $fieldVal, 'error', "");
            return array('failed' => true, 'name' => 'MisCarriageDateAndReason', 'value' => $fieldVal);
        }
        $miscarriage = $parts[0];
        $reason = $parts[1];
        Yii::log("miscarriage_date=". $miscarriage . "; reason=" . $reason, 'info', "");
        
        $miscarriage = trim($miscarriage);
        
        //TODO: check miscarriage_date's date-format
        
        if($status !== null) {
            $status->miscarriage_date = $miscarriage;
            $status->miscarriage_reason = $reason;
            return null;
        } else {
            $result = array();
            $field = array('name' => 'miscarriage_date', 'value' => $miscarriage);
            $result[] = $field;
            $field = array('name' => 'miscarriage_reason', 'value' => $reason);
            $result[] = $field;
            return $result;
        }
    }
    
    public function compound_NumKidsBornMF($status, $fieldVal) {
        if(!$fieldVal) return null;
        $parts = explode("|", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse numKidsBornMF: " . $fieldVal, 'error', "");
            return array('failed' => true, 'name' => 'NumKidsBornMF', 'value' => $fieldVal);
        }
        $numKidsM = $parts[0];
        $numKidsM = intval($numKidsM);
        
        $numKidsF = $parts[1];
        $numKidsF = intval($numKidsF);
        
        if($status !== null) {
            $status->num_kids_born_m = $numKidsM;
            $status->num_kids_born_f = $numKidsF;
            return null;
        } else {
            $result = array();
            $field = array('name' => 'num_kids_born_m', 'value' => $numKidsM);
            $result[] = $field;
            $field = array('name' => 'num_kids_born_f', 'value' => $numKidsM);
            $result[] = $field;
            return $result;
        }
    }
    
    public function compound_DeathDateAndReason($status, $fieldVal) {
        if(!$fieldVal) return null;
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse DeathDateAndReason: " . $fieldVal, 'error', "");
            return array('failed' => true, 'name' => 'DeathDateAndReason', 'value' => $fieldVal);
        }
        $death = $parts[0];
        $reason = $parts[1];
        
        $death = trim($death);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        
        if($status !== null) {
            $status->death_date = $death;
            $status->death_reason = $reason;
            return null;
        } else {
            $result = array();
            $field = array('name' => 'death_date', 'value' => $death);
            $result[] = $field;
            $field = array('name' => 'death_reason', 'value' => $reason);
            $result[] = $field;
            return $result;
        }
    }
    
    public function compound_SaleDateAndPrice($status, $fieldVal) {
        if(!$fieldVal) return null;
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse SaleDateAndPrice: " . $fieldVal, 'error', "");
            return array('failed' => true, 'name' => 'SaleDateAndPrice', 'value' => $fieldVal);
        }
        $sold = $parts[0];
        $salePrice = $parts[1];
        
        $sold = trim($sold);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        
        if($status !== null) {
            $status->sale_date = $sold;
            $status->sale_price = $salePrice;
            return null;
        } else {
            $result = array();
            $field = array('name' => 'sale_date', 'value' => $sold);
            $result[] = $field;
            $field = array('name' => 'sale_price', 'value' => $salePrice);
            $result[] = $field;
            return $result;
        }
    }
}
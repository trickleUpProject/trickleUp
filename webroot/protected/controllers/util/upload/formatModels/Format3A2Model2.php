<?php

class Format3A2Model2 {

    const CELL_TYPE = 'cellType';
    const CELL_ADDRESS = 'cellAddress';
    const CELL_HANDLER = 'cellHandler';
    const CELL_DB_COL_NAME = 'cellDBColName';
    const CELL_VALIDATOR = 'cellValidator';
    const CELL_VALIDATOR_IN_HANDLER = 'cellValidatorInHandler';
    
    const COL_RANGE = 'colRange';
    const HEADER_ROW = 'headerRow';
    const ROW_RANGE = 'rowRange';
    const ROW_HANDLERS = 'rowHandlers';
    
    const CELL_TYPE_SIMPLE = 'simple';
    const CELL_TYPE_RANGE = 'range';
    const CELL_TYPE_COMPLEX = 'complex';
    const CELL_TYPE_COMPOUND = 'compound';
    
    // special areas, if any
    const RANGE_LIVESTOCK_STATUS = 'livestockStatusRange';
    
    private $cellMap;


    public function __construct() {
        
        $this->cellMap = array(
            'shed_condition' => array(
                            self::CELL_TYPE => self::CELL_TYPE_COMPLEX, 
                            self::CELL_ADDRESS => 'E15', 
                            self::CELL_HANDLER => 'complex_ShedCondition'
                            ),
            'maintenance_cleanliness' => array(
                            self::CELL_TYPE => self::CELL_TYPE_COMPLEX, 
                            self::CELL_ADDRESS => 'I15',
                            self::CELL_HANDLER => 'complex_MaintenanceCleanliness'
                            ),
            'KMn04_application' => array(
                            self::CELL_TYPE => self::CELL_TYPE_COMPLEX, 
                            self::CELL_ADDRESS => 'A16',
                            self::CELL_HANDLER => 'complex_KMn04Application'
                            ),
            'separation_if_pregnant' => array(
                            self::CELL_TYPE => self::CELL_TYPE_COMPLEX, 
                            self::CELL_ADDRESS => 'A15',
                            self::CELL_HANDLER => 'complex_SeparationIfPregnant'
                            ),
                        
            //////////////////////////////////////////////////
        
            self::RANGE_LIVESTOCK_STATUS => array(
                self::COL_RANGE => array(1,12),
                self::HEADER_ROW => 4,
                self::ROW_RANGE => array(5, 14),
                self::ROW_HANDLERS => array(
                    'r5' => array( // age_months
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE, 
                                    self::CELL_DB_COL_NAME => 'age_months',
                                    self::CELL_HANDLER => 'getSimpleCellValue', 
                                    self::CELL_VALIDATOR => 'getInt'
                                    ),
                    'r6' => array( // weight_kg
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_DB_COL_NAME => 'weight_kg',
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getDecimal'
                    ),
                    'r7' => array( // deworm
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_DB_COL_NAME => 'deworm',
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getDate'
                    ),
                    'r8' => array( // problem_conceiving
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_DB_COL_NAME => 'problem_conceiving',
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getYesNo'
                    ),
                    'r9' => array( // concentrate_during_pregnancy
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_DB_COL_NAME => 'concentrate_during_pregnancy',
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getYesNo'
                    ),
                    'r10' => array( // miscarriage_date, miscarriage_reason
                                    self::CELL_TYPE => self::CELL_TYPE_COMPOUND,
                                    self::CELL_HANDLER => 'compound_MisCarriageDateAndReason',
                                    self::CELL_VALIDATOR => self::CELL_VALIDATOR_IN_HANDLER
                    ),
                    'r11' => array( // delivery_date
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_DB_COL_NAME => 'delivery_date',
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getDate'
                    ),
                    'r12' => array( // num_kids_born_m, num_kids_born_f
                                    self::CELL_TYPE => self::CELL_TYPE_COMPOUND,
                                    self::CELL_HANDLER => 'compound_NumKidsBornMF',
                                    self::CELL_VALIDATOR => self::CELL_VALIDATOR_IN_HANDLER
                    ),
                    'r13' => array( // death_date, death_reason
                                    self::CELL_TYPE => self::CELL_TYPE_COMPOUND,
                                    self::CELL_HANDLER => 'compound_DeathDateAndReason',
                                    self::CELL_VALIDATOR => self::CELL_VALIDATOR_IN_HANDLER
                    ),
                    'r14' => array( // sale_date, sale_price
                                    self::CELL_TYPE => self::CELL_TYPE_COMPOUND,
                                    self::CELL_HANDLER => 'compound_SaleDateAndPrice',
                                    self::CELL_VALIDATOR => self::CELL_VALIDATOR_IN_HANDLER
                    )

                )
            ),
                        
            // other?
        );
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
        try {
            // "Shed condition: ______ / 5"  // do I seriously have to translate a count of underscores into a number?
            $parts = explode(':', $str);
            $part = $parts[1];
            $parts = explode('/', $part);
            $result = trim($parts[0]);
            $report->shed_condition = $result;
            return null;
        } 
        catch(Exception $e) {
            return array('failed' => true, 'name' => 'ShedCondition', 'value' => $str);
        }
    }
    
    public function complex_MaintenanceCleanliness($report, $str) {
        if(!$str) return null;
        try {
            // "Maintenance & Cleanliness (Y/N):"
            $parts = explode(':', $str);
            $result = trim($parts[1]);
            $report->maintenance_cleanliness = $result;
            return null;
        } 
        catch(Exception $e) {
            return array('failed' => true, 'name' => 'MaintenanceCleanliness', 'value' => $str);
        }
    }
    
    public function complex_KMn04Application($report, $str) {
        if(!$str) return null;
        try {
            // "KMnO4 appication before and after delivery (Y/N):"
            $parts = explode(':', $str);
            $result = trim($parts[1]);
            $report->KMn04_application = $result;
            return null;
        } 
        catch(Exception $e) {
            return array('failed' => true, 'name' => 'KMn04Application', 'value' => $str);
        }
    }
    
    public function complex_SeparationIfPregnant($report, $str) {
        if(!$str) return null;
        try {
            // Separation of pregnant goat (Y/N / N.A.): N
            $parts = explode(':', $str);
            $result = trim($parts[1]);
            $report->separation_if_pregnant = $result;
            return null;
        } 
        catch(Exception $e) {
            return array('failed' => true, 'name' => 'SeparationIfPregnant', 'value' => $str);
        }
    }
    
    
    
    public function compound_MisCarriageDateAndReason($status, $fieldVal) {
        if(!$fieldVal) return null;
        $parts = explode("&", $fieldVal);
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
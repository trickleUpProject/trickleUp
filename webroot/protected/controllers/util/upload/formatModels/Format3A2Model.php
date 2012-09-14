<?php

class Format3A2Model { 
    
    private $cellMap;
    private $dbFieldsForRowNums;
    
    
    public function __construct() {
        
        $this->cellMap = array(
          /////  global values ////
          "business_number" => 'F2',
          "participant_name" => 'B2',
          //"livestock_type" => array(array(), array()), // parse out of sheet-title
          "shed_condition" => 'E15', // requires parsing the cell-value! "Shed condition: ______ / 5"
          "maintenance_cleanliness" => 'I15', // // requires parsing the cell-value! "Maintenance & Cleanliness (Y/N):"
          "KMnO4_application" => 'A16', // requires parsing the cell-value! "KMnO4 appication before and after delivery (Y/N):"
          ///////////////////////////////
          "staff" => 'I2',
          "year" => 'L2',   // i.e., infer from L2, namely 'Period'
          "month" => 'L2',  // i.e., infer from L2, namely 'Period'
          "quarter" => 'L2',// i.e., infer from L2, namely 'Period'
          "livestock_number" => array(array(1,13), array(4)),
          "age_in_months" => array(array(), array()),
          "weight_kg" => array(array(), array()),
          "deworming_done" => array(array(), array()),
          "problem_conceiving" => array(array(), array()),
          "concentrate_during_pregnancy" => array(array(), array()),
          "separate_during_pregnancy" => array(array(), array()),
          "miscarriage" => array(array(), array()),
          "miscarriage_reason" => array(array(), array()),
          "delivery_date date" => array(array(), array()),
          "num_kids_m" => array(array(), array()),
          "num_kids_f" => array(array(), array()),
          "death" => array(array(), array()),
          "reason_for_death" => array(array(), array()),
          "sold" => array(array(), array()),
          "sale_price" => array(array(), array())
        );

        $this->dbFieldsForRowNums = array(
            "r5" => array("name" => "age_in_months", "type" => "int"),
            "r6" => array("name" => "weight_kg", "type" => "int"),
            "r7" => array("name" => "deworming_done", "type" => "varchar"),
            "r8" => array("name" => "problem_conceiving", "type" => "varchar"),
            "r9" => array("name" => "concentrate_during_pregnancy", "type" => "varchar"),
            "r10" => array("name" => 'compound', 'methodName' => 'compound_handle_MiscarriageAndReason'),
            "r11" => array("name" => "delivery_date", "type" => "date"),
            "r12" => array("name" => 'compound', 'methodName' => 'compound_handle_NumKidsBornMF'),
            "r13" => array("name" => 'compound', 'methodName' => 'compound_handle_DeathAndReason'),
            "r14" => array("name" => 'compound', 'methodName' => 'compound_handle_SoldSalePrice')
        );
    }
    
    public function getCellCoordsforDBField($field) {
        return $this->cellMap[$field];
    }
    
    public function getColDBFieldForRowNum($rowNum) {
        $key = 'r' . $rowNum;
        Yii::log("seeking with key: " . $key, 'info', "");
        if(!isset($this->dbFieldsForRowNums[$key])) {
            return null;
        }
        return $this->dbFieldsForRowNums[$key];
    }
    
    // START:
    // special methods called typically only via reflection,
    //  when there's no one-to-one relationship btw fieldName and modelField
    
    public function compound_handle_MiscarriageAndReason($inst, $fieldVal) {
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse MiscarriageAndReason: " . $fieldVal, 'error', "");
            return array('error' => "Format-Error: couldn't parse MiscarriageAndReason");
        }
        $miscarriage = $parts[0];
        $reason = $parts[1];
        Yii::log("miscarriage=". $miscarriage . "; reason=" . $reason, 'info', "");
        
        $miscarriage = trim($miscarriage);
        
        if($miscarriage != 'Y' && $miscarriage != 'N') {
            Yii::log("miscarriage value neither 'Y' nor 'N'", 'error', "");
            return array('error' => "Format-Error: miscarriage must be either 'Y' or 'N'");
        }
        if($inst !== null) {
            $inst->miscarriage = $miscarriage;
            $inst->miscarriage_reason = $reason;
            return null;
        } else {
            $result = array();
            $field = array('name' => 'miscarriage', 'value' => $miscarriage);
            $result[] = $field;
            $field = array('name' => 'miscarriage_reason', 'value' => $reason);
            $result[] = $field;
            return $result;
        }
    }
    
    public function compound_handle_NumKidsBornMF($inst, $fieldVal) {
        $parts = explode("|", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse numKidsBornMF: " . $fieldVal, 'error', "");
            return array('error' => "Cell Format-Error: couldn't parse numKidsBornMF");
        }
        $numKidsM = $parts[0];
        $numKidsM = intval($numKidsM);
        
        $numKidsF = $parts[1];
        $numKidsF = intval($numKidsF);
        
        if($inst !== null) {
            $inst->num_kids_m = $numKidsM;
            $inst->num_kids_f = $numKidsF;
        } else {
            $result = array();
            $field = array('name' => 'num_kids_m', 'value' => $numKidsM);
            $result[] = $field;
            $field = array('name' => 'num_kids_f', 'value' => $numKidsM);
            $result[] = $field;
            return $result;
        }

    }
    
    public function compound_handle_DeathAndReason($inst, $fieldVal) {
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse deathAndReason: " . $fieldVal, 'error', "");
            return array('error' => "Cell Format-Error: couldn't parse deathAndReason");
        }
        $death = $parts[0];
        $reason = $parts[1];
        
        $death = trim($death);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        
        if($inst !== null) {
            $inst->death = $death;
            $inst->reason_for_death = $reason;
        } else {
            $result = array();
            $field = array('name' => 'death', 'value' => $death);
            $result[] = $field;
            $field = array('name' => 'reason_for_death', 'value' => $reason);
            $result[] = $field;
            return $result;
        }

    }
    
    public function compound_handle_SoldSalePrice($inst, $fieldVal) {
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse soldSalePrice: " . $fieldVal, 'error', "");
            return array('error', "Cell Format-Error: couldn't parse soldSalePrice");
        }
        $sold = $parts[0];
        $salePrice = $parts[1];
        
        $sold = trim($sold);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        
        if($inst !== null) {
            $inst->sold = $sold;
            $inst->sale_price = $salePrice; // float type! should instead be, e.g., DECIMAL(8,2)
            /*
             DECIMAL would suit your needs better -- from [2]: "The DECIMAL and
            NUMERIC types [...] are used to store values for which it is important
            to preserve exact precision, for example with monetary data."
            */
        } else {
            $result = array();
            $field = array('name' => 'sole', 'value' => $sold);
            $result[] = $field;
            $field = array('name' => 'sale_price', 'value' => $salePrice);
            $result[] = $field;
            return $result;
        }

    }
    
    // END: special compound methods
    
}
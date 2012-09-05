<?php

class Format3A2Model { 
    
    private $cellMap;
    private $dbFieldsForRowNums;
    
    
    public function __construct() {
        
        $this->cellMap = array(
          "business_number" => array(array(), array()),
          "participant_name" => array(array(), array()),
          "staff" => array(array(), array()),
          "month" => array(array(), array()),
          "year" => array(array(), array()),
          "quarter" => array(array(), array()),
          "livestock_number" => array(array(1,13), array(4)), // TODO: colSpecs should be strictly numeric?
          "livestock_type" => array(array(), array()),
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
          "sale_price" => array(array(), array()),
          "shed_condition" => array(array(), array()),
          "maintenance_cleanliness" => array(array(), array()),
          "KMnO4_application" => array(array(), array())
        );

        $this->dbFieldsForRowNums = array(
            "r5" => array("name" => "age_in_months", "type" => "int"),
            "r6" => array("name" => "weight_kg", "type" => "int"),
            "r7" => array("name" => "deworming_done", "type" => "varchar"),
            "r8" => array("name" => "problem_conceiving", "type" => "varchar"),
            "r9" => array("name" => "concentrate_during_pregnancy", "type" => "varchar"),
            "r10" => array("name" => 'compound', 'methodName' => 'compound_handleMiscarriageAndReason'),
            "r11" => array("name" => "delivery_date", "type" => "date"),
            "r12" => array("name" => 'compound', 'methodName' => 'compound_handleNumKidsBornMF'),
            "r13" => array("name" => 'compound', 'methodName' => 'compound_handleDeathAndReason'),
            "r14" => array("name" => 'compound', 'methodName' => 'compound_handleSoldSalePrice')
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
    
    public function compound_handleMiscarriageAndReason($inst, $fieldVal) {
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse miscarriageAndReason: " . $fieldVal, 'error', "");
            return;
        }
        $miscarriage = $parts[0];
        $reason = $parts[1];
        Yii::log("miscarriage=". $miscarriage . "; reason=" . $reason, 'info', "");
        $miscarriage = trim($miscarriage);
        if($miscarriage != 'Y' && $miscarriage != 'N') {
            Yii::log("miscarriage value neither 'Y' nor 'N'", 'error', "");
            return;
        }
        $inst->miscarriage = $miscarriage;
        $inst->miscarriage_reason = $reason;
    }
    
    public function compound_handleNumKidsBornMF($inst, $fieldVal) {
        $parts = explode("|", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse numKidsBornMF: " . $fieldVal, 'error', "");
            return;
        }
        $numKidsM = $parts[0];
        $numKidsF = $parts[1];
        
        $inst->num_kids_m = intval($numKidsM);
        $inst->num_kids_f = intval($numKidsF);
    }
    
    public function compound_handleDeathAndReason($inst, $fieldVal) {
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse deathAndReason: " . $fieldVal, 'error', "");
            return;
        }
        $death = $parts[0];
        $reason = $parts[1];
        
        $death = trim($death);
        if(false) {
            // TODO: test for date-format
            Yii::log("bad date-format", 'error', "");
            return;
        }
        $inst->death = $death;
        $inst->reason_for_death = $reason;
    }
    
    public function compound_handleSoldSalePrice($inst, $fieldVal) {
        $parts = explode("&", $fieldVal);
        if(count($parts) < 2) {
            Yii::log("Cell Format-Error: couldn't parse soldSalePrice: " . $fieldVal, 'error', "");
            return;
        }
        $sold = $parts[0];
        $salePrice = $parts[1];
        
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
    
}
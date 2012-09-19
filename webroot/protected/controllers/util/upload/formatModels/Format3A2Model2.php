<?php

class Format3A2Model2 {

    const CELL_TYPE = 'cellType';
    const CELL_ADDRESS = 'cellAddress';
    const CELL_HANDLER = 'cellHandler';
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
                self::COL_RANGE => array(2,13),
                self::HEADER_ROW => 4,
                self::ROW_RANGE => array(5, 14),
                self::ROW_HANDLERS => array(
                    'r5' => array( // age_months
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE, 
                                    self::CELL_HANDLER => 'getSimpleCellValue', 
                                    self::CELL_VALIDATOR => 'getInt'
                                    ),
                    'r6' => array( // weight_kg
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getDecimal'
                    ),
                    'r7' => array( // deworm
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getDate'
                    ),
                    'r8' => array( // problem_conceiving
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
                                    self::CELL_HANDLER => 'getSimpleCellValue',
                                    self::CELL_VALIDATOR => 'getYesNo'
                    ),
                    'r9' => array( // concentrate_during_pregnancy
                                    self::CELL_TYPE => self::CELL_TYPE_SIMPLE,
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
                        
            //'livestock_number' => array(),
            //'livestock_type' => array()
        );
    }
    
    
    public function getCellMap() {
        return $this->cellMap;
    }
    
    
    public function getInt() {
        
    }
    
    public function getDecimal() {
        
    }
    
    public function getDate() {
        
    }
    
    public function getYesNo() {
        
    }
    
    
    
    public function complex_ShedCondition() {
        
    }
    
    public function complex_MaintenanceCleanliness() {
        
    }
    
    public function complex_KMn04Application() {
        
    }
    
    public function complex_SeparationIfPregnant() {
        
    }
    
    
    
    
    public function compound_MisCarriageDateAndReason() {
        
    }
    
    public function compound_NumKidsBornMF() {
        
    }
    
    public function compound_DeathDateAndReason() {
        
    }
    
    public function compound_SaleDateAndPrice() {
        
    }
}
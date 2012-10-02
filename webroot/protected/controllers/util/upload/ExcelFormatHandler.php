<?php
abstract class ExcelFormatHandler {
    
    public function __construct() {
        
    }
    
    abstract function import($objPHPExcel, $importConfig);
    
    abstract function getModel();
}
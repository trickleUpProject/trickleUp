<?php

class Format3A2Handler2 extends ExcelFormatHandler {
    
    // DB-Model
    const MODEL_NAME = "LivestockTracking";
    
    // Model relating the given Excel-doc format to its DB-table counterpart
    private $formatModel;
    private $refFormatModel;
    
    
    public function __construct() {
        parent::__construct();
        
        Yii::log("importing Format3A2Model", 'info', "");
        Yii::import('application.controllers.util.upload.formatModels.Format3A2Model',true);
        
        Yii::log("instantiating Format3A2Model", 'info', "");
        $this->formatModel = new Format3A2Model();
        $this->refFormatModel = new ReflectionClass('Format3A2Model');
    }
    
    
    protected function extractShedCondition($str) {
         // "Shed condition: ______ / 5"  // do I seriously have to translate a count of underscores into a number?
         $parts = explode(':', $str);
         $part = $parts[1];
         $parts = explode('/', $part);
         $result = trim($parts[0]);
         return $result;
    }
    
    protected function extractMaintenanceCleanliness($str) {
        // "Maintenance & Cleanliness (Y/N):"
        $parts = explode(':', $str);
        $result = trim($parts[1]);
        return $result;
    }
    
    protected function extractKMn04Application($str) {
        // "KMnO4 appication before and after delivery (Y/N):"
        $parts = explode(':', $str);
        $result = trim($parts[1]);
        return $result;
    }
    
    protected function getSimpleGlobalValue($objWorksheet, $fieldName) {
        $cellId = $this->formatModel->getCellCoordsforDBField($fieldName);
        $cell = $objWorksheet->getCell($cellId);
        return $cell->getValue();
    }
    
    protected function getGlobalValues($objWorksheet) {
        
        $globalValues = array();
        
        $globalValues["business_number"] = $this->getSimpleGlobalValue($objWorksheet, "business_number");
        
        //TODO: lookup participant_id ?  OR: presume we have it already via UI-clickstream leading to this use-case
        $globalValues["participant_name"] = $this->getSimpleGlobalValue($objWorksheet, "participant_name");
        
        //TODO: what to do about "Goat & Sheep"?  
        //  a single sheet seems to be used for both (or perhaps a mixture of the two)
        //  - have them add an attribute (row): "goat or sheep?: goat|sheep" ?
        
        $sheetTitle = $objWorksheet->getTitle();
        $sheetTitleParts = explode('-', $sheetTitle);
        $livestockType = trim($sheetTitleParts[2]);
        $globalValues["livestock_type"] = strtolower($livestockType);
        
        $shedCondition = $this->getSimpleGlobalValue($objWorksheet, "shed_condition");
        $shedCondition = $this->extractShedCondition($shedCondition);
        $globalValues["shed_condition"] = $shedCondition;
        
        $maint = $this->getSimpleGlobalValue($objWorksheet, "maintenance_cleanliness");
        $maint = $this->extractMaintenanceCleanliness($maint);
        $globalValues["maintenance_cleanliness"] = $maint;
        
        $kmn = $this->getSimpleGlobalValue($objWorksheet, "KMnO4_application");
        $kmn = $this->extractKMn04Application($kmn);
        $globalValues["KMnO4_application"] = $kmn;
        
        return $globalValues;
    }
    
    protected function setGlobalValues($globalValues, $badRows, $inst) { // need to pass in obj-params by reference?
        
        if($badRows) {
            // TODO: add a "fieldDescr" for each global value, 
            //   but only those really needed for the user to be able to 'fix' bad-row values
            //   (while viewing those values in the DataTable)
            
            $fieldDesc = array('name' => 'participant_name', 'value' => $globalValues["participant_name"]);
            $badRows[count($badRows)-1][] = $fieldDesc;
        } 
        else if($inst) {
            
            $inst->business_number = $globalValues["business_number"];
            $inst->participant_name = $globalValues["participant_name"];
            
            //TODO: infer these three from "Period:" ?
            $inst->year = 2012;
            $inst->quarter = 1;
            $inst->month = 1;
            
            $inst->livestock_type = $globalValues["livestock_type"];
        }
    }
    
    public function &import($objPHPExcel, $importConfig) {
    
        $importTime = time();
        
        Yii::import('application.vendors.PHPExcel',true);
    
        $objWorksheet = $objPHPExcel->getActiveSheet(); // TODO: do this for each sheet in current Excel-doc
    
        $lsIds = $this->formatModel->getCellCoordsforDBField('livestock_number');
        Yii::log("lsIds: " . print_r($lsIds, true), 'info', "");
        
        $cols = $lsIds[0];
        $rows = $lsIds[1]; // expecting only single row, simply based on knowledge of current format
        $row = $rows[0]; // the row the livestock_numbers are in
        
        $row++; // the first row of this livestock_number's property-values
        
        $tmpLivestockNum = 0;
        
        $globalValues = $this->getGlobalValues($objWorksheet);
        
        $badRows = array();
        //Yii::log("initted badRows: " . print_r($badRows, true), 'info', "");
        
        
        // iterate through (i.e., across) the livestock-numbers ("Pig No." or other such)
        for($i = $cols[0]; $i < $cols[1]; $i++) {
            
            $badRow = null;
            $inst = new LivestockTracking();
            
            $cell = $objWorksheet->getCellByColumnAndRow($i, ($row-1)); // in the row the livestock_numbers are in
            $livestock_number = $cell->getValue();
            
            $inst->livestock_number = $livestock_number;
            
            $cell = null;
            
            // in current column, iterate through (i.e., down through) the property-values for this model-instance
            for($j = $row; ; $j++) {
                
                $field = $this->formatModel->getColDBFieldForRowNum($j);
                
                if($field == null) {
                    Yii::log("no field found for rowNum: " . $j, 'error', "");
                    break; // found end of row-sequence of properties for this $inst (or some error)
                }
                Yii::log("field: " . print_r($field, true), 'info', "");
                
                $cell = $objWorksheet->getCellByColumnAndRow($i, $j);
                if($cell == null) {
                    Yii::log("no cell found at col=" . $i . "; row=" . $j, 'error', "");
                    break;
                }
                
                if($field['name'] != "compound") {
                    
                    //TODO: handle cell's data-format issues, if any: esp. date-format
                    // - should be: yyyy-mm-dd  (possible i18n issues here!);
                    // - generally, if any format-issues, will need to accumulate them and
                    //   direct to View for having user correct them; then, re-process using
                    //   already uploaded copy of given Excel-doc (so, need to keep track of uploaded
                    //     but not yet processed Excel-docs; e.g., have error-correction form-submission
                    //     also send file-name of relevant recently uploaded file)
                    
                    //TODO: if bad format, somehow mark latest pushed to badRow as bad
                    
                    $inst->setAttribute($field['name'], $cell->getValue());
                    
                } else {
                    
                    $methodName = $field['methodName'];
                    Yii::log("calling compound method: " . $methodName, 'info', "");
                    $refMethod = $this->refFormatModel->getMethod($methodName);
                    $result = $refMethod->invokeArgs($this->formatModel, array($inst, $cell->getValue()));
                    
                    if($result !== null) {
                        if($badRow == null) {
                            $badRows[] = array();
                            $badRow = array(); // having only a boolean effect now
                        }
                        $methodNameParts = explode('_', $methodName);
                        $fieldName = $methodNameParts[2]; // after "compound" and "handle"
                        $val = $cell->getValue();
                        
                        $fieldDesc = array('name' => 'livestock_number', 'value' => $livestock_number);
                        $badRows[count($badRows)-1][] = $fieldDesc;
                        
                        $fieldDesc = array('name' => $fieldName, 'value' => $val, 'msg' => $result['msg']);
                        $badRows[count($badRows)-1][] = $fieldDesc;
                        
                        Yii::log("added fieldDesc to latest badRow: " . print_r($badRows[count($badRows)-1], true), 'info', "");
                        
                    } else {
                        Yii::log("no result from compound method: " . $methodName, 'info', "");
                    }
                }
                
            } // end row-loop
            
            if($cell == null) { // because "no cell found at ..." (above)
                break;
            }
            
            //TODO: you either have to store all the fields you can, first time in, in the $inst
            //  and then, second time in (while 'fixing'), just update or set the fields you couldn't
            //  at first set; or don't store any fields in the $inst, and carry all those fields
            //  back and forth during the 'fixing' process, and then store them all at once in the
            //  second ('fixing') step; best to store what you can in the $inst, and pass back and forth
            //  only the values required for fixing; then, in second ('fixing') step, lookup ('find()')
            //  each $inst to be fixed and update it
            
            if($badRow !== null) {
                
                $this->setGlobalValues($globalValues, $badRows, null); // need to pass $badRows by reference?
                
                //TODO: need to store ALL cell-values of badRow in data (while iterating, above)
                //  so have all those values second time in, after the 'fix'; when cannot split
                //  a cell-value needing splitting, you store the non-split value, and otherwise
                //  you store the values separted, ready for storage in relevant normal table;
                //  when updating, you make use of the returning value derived from last_insert_id
                //  in bad_row, to fetch any additional needed data; will need to then re-attempt the
                //  splitting of relevant cells; ultimately, if 'fixes' worked, you have all values
                //  required for storing a new $inst
                
                $data = CJSON::encode($badRows[count($badRows)-1]);
                $importFile = $importConfig['import_file'];
                $importFormat = $importConfig['import_format'];
                
                $badRowModel = new BadRow();
                
                $badRowModel->import_time = $importTime;
                $badRowModel->data = $data;
                $badRowModel->import_file = $importFile;
                $badRowModel->import_format = $importFormat;
                
                Yii::log("saving badRowModel for importFile: " . $importFile, 'info', "");
                if(!$badRowModel->save()) {
                    Yii::log(print_r($badRowModel->getErrors(), true), 'error', "");
                }
                
                //TODO: fetch last_insert_id in bad_row and add it to latest badRow in badRows,
                //  so you can find it later, when the 'fix' comes back (via AJAX from DataTable)
                
                $badRow = null; // clear for next iteration
                
            } else {

                $this->setGlobalValues($globalValues, null, $inst); // need to pass $inst by reference?
                
                /*
                //TODO: temporary: needed to satisfy all NOT NULLs and uniqueness constraints
                $inst->business_number = $tmpLivestockNum;
                $inst->participant_name = 'Some Participant Name - ' . $tmpLivestockNum;
                $inst->year = 2012;
                $inst->quarter = 1;
                $inst->month = 1;
                //$inst->livestock_number = $tmpLivestockNum++;
                $inst->livestock_type = 'pig'; // relates to which sheet is current
                */
                
                //TODO: if badRow (how to know it here though?), don't save;
                // should have a field "import_validated", default value false;
                // could use single timestamp for storing these rows; then can later
                // set them to import_validated, once all badRows have been fixed; or
                // don't bother with that and just store what you can when you can, and
                // store the rest later, once their values have been fixed; then again,
                // overall procedure could be interrupted (e.g., by end-user); then, could
                // store in DB in table bad_rows (import_timestamp, data), with 'data' being
                // JSON of badRow, so user could later get back to fixing those badRows; then,
                // using that import_timestamp, you'd set import_validated on all the newly
                // completely validated row-set
                
                Yii::log("saving inst: " . $inst->participant_name, 'info', ""); // should log also livestock_number
                $inst->save();
            }

        } // end column loop
    
        if(count($badRows) > 0) {
            Yii::log("returning badRows: " . print_r($badRows, true), 'info', "");
            return $badRows;
        } else {
            return null;
        }
    }
    
}
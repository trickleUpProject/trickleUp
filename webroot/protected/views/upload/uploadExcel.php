<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Upload Excel</title>

<style type="text/css" title="currentStyle">
	@import "/js/jquery-datatables-editable-read-only/media/css/demo_page.css";
	@import "/js/jquery-datatables-editable-read-only/media/css/demo_table.css";
	@import "/js/jquery-datatables-editable-read-only/media/css/themes/base/jquery-ui.css";
	@import "/js/jquery-datatables-editable-read-only/media/css/themes/smoothness/jquery-ui-1.7.2.custom.css";
</style>

<style type="text/css">
#saveUnsavedChanges {
    width: 160px; 
    cursor: pointer; 
    border: 3px solid black; 
    text-align:center; 
    color: blue; 
    visibility: hidden; 
    background-color: yellow; 
    font-weight:bold
}

#saveUnsavedChanges:hover {
    color: yellow;
    background-color: blue
}

#saveChangesResp {
    background-color: yellow;
    color: blue;
    font-weight:bold;
}

.formatError {
    background-color: red
}

</style>

<script src="/js/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="/js/DataTables-1.9.3/media/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="/js/jquery.jeditable.mini.js" type="text/javascript"></script>
<script src="/js/jquery-datatables-editable-read-only/media/js/jquery-ui.js" type="text/javascript"></script>
<script src="/js/jquery-validation-1.9.0/jquery.validate.js" type="text/javascript"></script>
<script src="/js/jquery-datatables-editable-read-only/media/js/jquery.dataTables.editable.js" type="text/javascript"></script>

 
<script type="text/javascript" charset="utf-8">

$(document).ready( function() {

    function log(msg) {
        if(console && console.log) {
            console.log(msg);
        }
    }

    formatCols = {};
    
    <?php
    if(property_exists($model, 'formatErrors') && $model->formatErrors != null) {
        print_r("//" . $model->formatErrors . "\n");
        echo "formatErrors = " . $model->formatErrors . ";\n";
        echo "hasFormatErrors = true;\n";
    } else {
       echo "hasFormatErrors = false;\n";
    }
   ?>

   //TODO: need to set all cells "read-only" that are part of a key in the DB,
   //  because you're not allowed to change them (in the DB); so, when errors therein
   //  need to delete row and re-insert?
    
    function execRemote(url, callback, dataToPOST) {
        var dataStr = dataToPOST != null ? ("data=" + JSON.stringify(dataToPOST)) : dataToPOST;
        var httpMethod = dataToPOST != null ? "POST" : "GET";
        //var httpMethod = "GET";
        //var contentTypeVal = dataToPOST != null ? 'text/json': 'application/x-www-form-urlencoded';
        var contentTypeVal = 'application/x-www-form-urlencoded'; //'text/json';
        $.ajax({
            type: httpMethod,
            url: url,
            data: dataStr,
            success: callback,
            contentType: contentTypeVal
        });
    }

    function setHasUnsavedChanges(set) {
        if(set === true) {
            $('#saveUnsavedChanges').css('visibility', 'visible');
            $('#saveChangesResp').html('');
        } else {
            $('#saveUnsavedChanges').css('visibility', 'hidden');
        }
    }
    
    // TEMPORARY: simulation of client-side persistence for offline-editing
    dataById = {};
    dirties = {}; // to avoid sending entire client-side copy to server for updating
    
    function initDataTable(model) {

        var myModel = model;
        
        $('#' + myModel).dataTable().makeEditable({
            
            //sUpdateURL: "UpdateData.php",
            sUpdateURL: function(value, settings) {
                return value; //Simulation of server-side response using a callback function
            },
            fnOnEdited: function(result, sOldValue, sNewValue, iRowIndex, iColumnIndex, iRealColumnIndex) {

                log(arguments);
                log(dataById);
                
                var rowId = $("#" + myModel + " tbody > tr:nth-child(" + (iRowIndex+1) + ")").attr('id');
                log("rowId: " + rowId);
                rowId = rowId.split("_"); // has model as prefix
                rowId = rowId[1];
                var rowData = dataById[myModel][rowId];
                
                var colName = formatCols[myModel][iColumnIndex];
                log("colName: " + colName);
                
                rowData[colName] = sNewValue;
                log('rowData[' + colName + ']: ' + rowData[colName]);
                
                var key = rowId + "-" + colName;

                if(!dirties[model]) dirties[model] = {};
                
                if(dirties[model][key]) {
                    dirties[key].value = sNewValue;
                } else {
                    dirties[model][key] = {
                        "id": rowId,
                        "colName": colName,
                        "value": sNewValue
                    };
                }
                
                setHasUnsavedChanges(true);
            }
        });
    }

    formatErrorFieldNames = {};
    
    function addTableRow(model, obj) {

        var newTr = $('<tr>');
        var id = obj['id'];
        newTr.attr('id', model + "_" + id);
        newTr.attr('class', 'odd_gradeX');
        
        var field = obj['field'];
        newTr.append($('<td>').text(field['value']));

        if(!formatErrorFieldNames[model]) formatErrorFieldNames[model] = {};
        formatErrorFieldNames[model][field['name']] = field['name']; // shortcut to ensure no duplicates (per model)

        $("#" + model).find('tbody').append(newTr);
    }

    function ensureTable(model) {

        $divClone = $("#demo").clone(false);
        $divClone.attr('id', model + "_demo");
        $divClone.css('display', 'block');
        
        //$divClone.find("*[id]").andSelf().each(function() {
        $divClone.find("*[id]").each(function() {
                $(this).attr('id',function(i,id) {
                    log("id: " + id);
                    var suffix, uscoreIdx;
                    if((uscoreIdx = id.lastIndexOf('_')) != -1) {
                        log("uscoreIdx: " + uscoreIdx);
                        suffix = id.substring(uscoreIdx+1);
                        id = model + "_" + suffix;
                    } else {
                        id = model;
                    }
                    log("new id: " + id);
                    return id;
                });
            }
        );
        
        $divClone.insertAfter('#demo');
    }
    
    function loadTable(model, data) {

        log(data);
        ensureTable(model);
        
        if(!dataById[model]) dataById[model] = {};
        
        for(var i = 0; i < data.length; i++) {
            var item = data[i];
            addTableRow(model, item);
            dataById[model][item['id']] = item;
        }
        
        //initDataTable(model); // but needs to be called here in async (AJAX) case
    }
    
    if(!hasFormatErrors) {
        //execRemote('/index.php?r=upload/ajaxReportData', loadTable);
    } else {

        log("handling formatErrors");

        // iterate over top-level keys, which would be table-names, each to be associated with an instance of DataTable?
        // use JQuery-clone() to create any additional needed DataTable instances? (each needs own id, for JQuery-access);
        // foreach such entry in formatErrors, always have each table's set of field-errors in an array, even if there's
        // only one entry (e.g., for "report" -- i.e., values appearing only once in a given report); then, loadTable()
        // for each such array, passing in table-name; maybe use table-name as value of 'id'
        
        for(var model in formatErrors) {
            loadTable(model, formatErrors[model]);
        }

        for(var model in formatErrorFieldNames) {

            var errorFieldNames = formatErrorFieldNames[model];

            if(!formatCols[model]) formatCols[model] = [];
            var hrdId = '#' + model + '_tblHeader';
            var ftrId = '#' + model + '_tblFooter';
            
            for(var fieldName in errorFieldNames) {
                $(hrdId).append($('<th>').text(errorFieldNames[fieldName]));
                $(ftrId).append($('<th>').text(errorFieldNames[fieldName]));

                formatCols[model].push(errorFieldNames[fieldName]);
            }
        }
        
        initDataTable(model);
    }

    function handleReportDataUpdateResp(data) {
        log("handleReportDataUpdateResp");
        log(data);

        if(data.result && data.result == "Changes Saved") { //TODO: avoid bare literal here
            $('#saveChangesResp').html(data.result);
            setHasUnsavedChanges(false);
        } else {
            log("WARN: not all cell format-fixes successful");
            log(data);
        }
    }
    
    $('#saveUnsavedChanges').click(function() {
        log('saveUnsavedChanges clicked');
        log("sending dirties:");
        log(dirties);
        var data = {
            op: "FORMAT_FIX",
            dirties: dirties
        };
        execRemote('/index.php?r=upload/ajaxReportDataUpdate', handleReportDataUpdateResp, data);
    });

} );

</script>

</head>
<body id="dt_example">
    
    <h3>Upload Excel</h3>
    
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'upload-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
    'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<div class="row">
		<?php echo $form->labelEx($model,'file'); ?>
		<?php echo $form->fileField($model,'file'); ?>
		<?php echo $form->error($model,'file'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Upload'); ?>
	</div>

<?php $this->endWidget(); ?>
</div>


<div id="unsavedChanges">
    <span id="saveUnsavedChanges">
        Save Unsaved Changes
    </span><br/>
    <span id="saveChangesResp"></span>
</div>

<div id="container">

    <!-- a 'prototype', cloned via JQuery as needed -->
    <div id="demo" style="display: none; width: 800px; overflow: auto">
        <table cellpadding="0" cellspacing="0" border="0" class="display" style="width: 800px" id="example">
        	<thead><tr id="example_tblHeader"></tr></thead>
        	<tfoot><tr id="example_tblFooter"></tr></tfoot>
        	<tbody></tbody>
        </table>
   </div>

</div>

</body>
</html>
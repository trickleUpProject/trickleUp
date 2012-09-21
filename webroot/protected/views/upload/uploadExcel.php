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

$(document).ready( function () {

    function log(msg) {
        if(console && console.log) {
            console.log(msg);
        }
    }

    formatCols = [];
    
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
   //  because you're not allowed to change them (in the DB)
    
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
    
    function initDataTable() {
        
        $('#example').dataTable().makeEditable({
            
            //sUpdateURL: "UpdateData.php", //On the code.google.com POST request is not supported so this line is commented out
            sUpdateURL: function(value, settings) {
                return value; //Simulation of server-side response using a callback function
            },
            fnOnEdited: function(result, sOldValue, sNewValue, iRowIndex, iColumnIndex, iRealColumnIndex) {

                log(arguments);
                log(dataById);
                
                var rowId = $("#example tbody > tr:nth-child(" + (iRowIndex+1) + ")").attr('id');
                log("rowId: " + rowId);
                var rowData = dataById[rowId];
                var colName = formatCols[iColumnIndex];
                log("colName: " + colName);
                rowData[colName] = sNewValue;
                
                var key = rowId + "-" + colName;
                
                if(dirties[key]) {
                    dirties[key].value = sNewValue;
                } else {
                    dirties[key] = {
                        "id": rowId,
                        "colName": colName,
                        "value": sNewValue
                    };
                }
                
                log('rowData[' + colName + ']: ' + rowData[colName]);
                setHasUnsavedChanges(true);
            }
        });
    }

    formatErrorFieldNames = {};
    
    function addTableRow(obj) {
        
        var newTr = $('<tr>');
        var id = obj['id'];
        newTr.attr('id', id);
        newTr.attr('class', 'odd_gradeX');
        
        var field = obj['field'];
        newTr.append($('<td>').text(field['value']));
        formatErrorFieldNames[field['name']] = field['name']; // shortcut to ensure no duplicates

        $("#example").find('tbody').append(newTr);
    }
    
    function loadTable(data) {
        log(data);
        var statuses = data['statuses'];
        for(var i = 0; i < statuses.length; i++) {
            var item = statuses[i];
            addTableRow(item);
            dataById[item['id']] = item;
        }
        //initDataTable(); // but needs to be called here in async (AJAX) case
    }
    
    if(!hasFormatErrors) {
        //execRemote('/index.php?r=upload/ajaxReportData', loadTable);
    } else {
        log("handling formatErrors");
        loadTable(formatErrors);
        for(var fieldName in formatErrorFieldNames) {
            $('#tblHeader').append($('<th>').text(formatErrorFieldNames[fieldName]));
            $('#tblFooter').append($('<th>').text(formatErrorFieldNames[fieldName]));
            formatCols.push(formatErrorFieldNames[fieldName]);
        }
        initDataTable();
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
    <span id="saveChangesResp">
        
    </span>
</div>

<div id="container">

    <div id="demo" style="width: 800px; overflow: auto">

        <table cellpadding="0" cellspacing="0" border="0" class="display" style="width: 800px" id="example">
        	<thead>
        		<tr id="tblHeader">
        			 
        		</tr>
        	</thead>
        	<tfoot>
        		<tr id="tblFooter">
        			 
        		</tr>
        	</tfoot>
        	<tbody>

        	</tbody>
        </table>

   </div>

</div>

</body>
</html>
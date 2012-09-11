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

    //TODO: need to set all cells "read-only" that are part of a key in the DB,
    //  because you're not allowed to change them (in the DB)
    
    var formatCols = [
         'participant_name',
         'livestock_type',
         'age_in_months',
         'weight_kg',
         'miscarriage'
    ];
    
    function addTableRow(obj) {
        $("#example").find('tbody')
        .append($('<tr>')          // TODO: reference formatCols here instead of literally
            .attr('id', obj['livestock_number'])
            .attr('class', 'odd_gradeX')
            .append($('<td>')
                .text(obj['participant_name'])
            )
            .append($('<td>')
                .text(obj['livestock_type'])
            )
            .append($('<td>')
                .text(obj['age_in_months'])
            )
            .append($('<td>')
                .text(obj['weight_kg'])
            )
            .append($('<td>')
                .text(obj['miscarriage'] ? obj['miscarriage'] : "null") // real nulls screw up the table-layout!
            )
        );
    }


    var ary = [
        {
        "id": "1",
        "livestock_number": "foo",
        "participant_name": "bar",
        "livestock_type": "baz",
        "age_in_months": "fubar",
        "weight_kg": "fubaz",
        "miscarriage": "fubaz1"
        },
        {
        "id": "2",
        "livestock_number": "foo2",
        "participant_name": "bar2",
        "livestock_type": "baz2",
        "age_in_months": "fubar2",
        "weight_kg": "fubaz2",
        "miscarriage": "fubaz2"
        }
    ];


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
    var dataById = {};
    var dirties = {}; // to avoid sending entire client-side copy to server for updating
    
    function loadTable(data) {
        
        log(data);

        for(var i = 0; i < data.length; i++) {
            var item = data[i];
            addTableRow(item);
            // TODO: generalize this: use appropriate key for current format
            dataById[item['livestock_number']] = item;
        }

        $('#example').dataTable().makeEditable({
            
            //sUpdateURL: "UpdateData.php", //On the code.google.com POST request is not supported so this line is commented out
            sUpdateURL: function(value, settings) {
                return value; //Simulation of server-side response using a callback function
            },
            fnOnEdited: function(result, sOldValue, sNewValue, iRowIndex, iColumnIndex, iRealColumnIndex) {

                log(arguments);
                
                var rowData = dataById[iRowIndex];
                var colName = formatCols[iColumnIndex];
                rowData[colName] = sNewValue;
                
                var key = iRowIndex + "-" + colName;
                
                if(dirties[key]) {
                    dirties[key].value = sNewValue;
                } else {
                    dirties[key] = {
                        "id": iRowIndex,
                        "colName": colName,
                        "value": sNewValue
                    };
                }
                
                log('rowData[' + colName + ']: ' + rowData[colName]);
                setHasUnsavedChanges(true);
            }
        });
    }

    //loadTable(ary);
    
    <?php 
        if($model->badRows) {
            echo "var badRows = true";
        } else {
            echo "var badRows = false";
        }
    ?>

    if(!badRows) {
        execRemote('/index.php?r=upload/ajaxReportData', loadTable);
    } else {
        log("handling badRows");
    }

    function handleReportDataUpdateResp(data) {
        log("handleReportDataUpdateResp");
        log(data);

        if(data.result && data.result == "Changes Saved") { //TODO: avoid bare literal here
            $('#saveChangesResp').html(data.result);
            setHasUnsavedChanges(false);
        } else {
            log("ERROR: problem with ajax-result:");
            log(data);
        }
    }
    
    $('#saveUnsavedChanges').click(function() {
        log('saveUnsavedChanges clicked');
        log("sending dirties:");
        log(dirties);
        execRemote('/index.php?r=upload/ajaxReportDataUpdate', handleReportDataUpdateResp, dirties);
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
</div><!-- form -->


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
        		<tr>
        			<th>Participant Name</th>
        			<th>Livestock Type</th>
        			<th>Age in Months</th>
        			<th>Weight (kg)</th>
        			<th>Miscarriage</th>
        		</tr>
        	</thead>
        	<tfoot>
        		<tr>
        			<th>Participant Name</th>
        			<th>Livestock Type</th>
        			<th>Age in Months</th>
        			<th>Weight (kg)</th>
        			<th>Miscarriage</th>
        		</tr>
        	</tfoot>
        	<tbody>
        		 <?php 
        		     $badRows = $model->badRows;
        		     print_r($badRows);
        		 ?>
        	</tbody>
        </table>

    </div>

</div>

</body>
</html>
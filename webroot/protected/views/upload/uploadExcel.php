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
    execRemote('/index.php?r=upload/ajaxReportData', loadTable);

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

			<div id="demo">

<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
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
	<!-- 
		<tr class="odd_gradeX" id="2">
			<td class="read_only"> A Trident(read only cell)</td>
			<td>Internet Explorer 4.0</td>
			<td>Win 95+</td>
			<td class="center">4</td>

			<td class="center">X</td>
		</tr>
		<tr class="even_gradeC" id="4">
			<td>Trident</td>
			<td>Internet Explorer 5.0</td>
			<td>Win 95+</td>
			<td class="center">5</td>

			<td class="center">C</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Trident</td>
			<td>Internet Explorer 5.5</td>
			<td>Win 95+</td>
			<td class="center">5.5</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Trident</td>
			<td class="read_only">Internet Explorer 6(read only cell)</td>
			<td>Win 98+</td>
			<td class="center">6</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Trident</td>
			<td>Internet Explorer 7</td>
			<td class="read_only">Win XP SP2+(read only cell)</td>
			<td class="center">7</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Trident</td>
			<td>AOL browser (AOL desktop)</td>
			<td>Win XP</td>
			<td class="center">6</td>

			<td class="center read_only">A(read only cell)</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Firefox 1.0</td>
			<td>Win 98+ / OSX.2+</td>
			<td class="center">1.7</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Firefox 1.5</td>
			<td>Win 98+ / OSX.2+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Firefox 2.0</td>
			<td>Win 98+ / OSX.2+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Firefox 3.0</td>
			<td>Win 2k+ / OSX.3+</td>
			<td class="center">1.9</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Camino 1.0</td>
			<td>OSX.2+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Camino 1.5</td>
			<td>OSX.3+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Netscape 7.2</td>
			<td>Win 95+ / Mac OS 8.6-9.2</td>
			<td class="center">1.7</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Netscape Browser 8</td>
			<td>Win 98SE+</td>
			<td class="center">1.7</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Netscape Navigator 9</td>
			<td>Win 98+ / OSX.2+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Mozilla 1.0</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Mozilla 1.1</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1.1</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Mozilla 1.2</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1.2</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Mozilla 1.3</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1.3</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Mozilla 1.4</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1.4</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Mozilla 1.5</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1.5</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Mozilla 1.6</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">1.6</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Mozilla 1.7</td>
			<td>Win 98+ / OSX.1+</td>
			<td class="center">1.7</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Mozilla 1.8</td>
			<td>Win 98+ / OSX.1+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Gecko</td>
			<td>Seamonkey 1.1</td>
			<td>Win 98+ / OSX.2+</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Gecko</td>
			<td>Epiphany 2.20</td>
			<td>Gnome</td>
			<td class="center">1.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Webkit</td>
			<td>Safari 1.2</td>
			<td>OSX.3</td>
			<td class="center">125.5</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Webkit</td>
			<td>Safari 1.3</td>
			<td>OSX.3</td>
			<td class="center">312.8</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Webkit</td>
			<td>Safari 2.0</td>
			<td>OSX.4+</td>
			<td class="center">419.3</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Webkit</td>
			<td>Safari 3.0</td>
			<td>OSX.4+</td>
			<td class="center">522.1</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Webkit</td>
			<td>OmniWeb 5.5</td>
			<td>OSX.4+</td>
			<td class="center">420</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Webkit</td>
			<td>iPod Touch / iPhone</td>
			<td>iPod</td>
			<td class="center">420.1</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Webkit</td>
			<td>S60</td>
			<td>S60</td>
			<td class="center">413</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Presto</td>
			<td>Opera 7.0</td>
			<td>Win 95+ / OSX.1+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Presto</td>
			<td>Opera 7.5</td>
			<td>Win 95+ / OSX.2+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Presto</td>
			<td>Opera 8.0</td>
			<td>Win 95+ / OSX.2+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Presto</td>
			<td>Opera 8.5</td>
			<td>Win 95+ / OSX.2+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Presto</td>
			<td>Opera 9.0</td>
			<td>Win 95+ / OSX.3+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Presto</td>
			<td>Opera 9.2</td>
			<td>Win 88+ / OSX.3+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Presto</td>
			<td>Opera 9.5</td>
			<td>Win 88+ / OSX.3+</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Presto</td>
			<td>Opera for Wii</td>
			<td>Wii</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Presto</td>
			<td>Nokia N800</td>
			<td>N800</td>
			<td class="center">-</td>

			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Presto</td>
			<td>Nintendo DS browser</td>
			<td>Nintendo DS</td>
			<td class="center">8.5</td>

			<td class="center">C/A<sup>1</sup></td>
		</tr>
		<tr class="even_gradeC" id="4">
			<td>KHTML</td>
			<td>Konqureror 3.1</td>
			<td>KDE 3.1</td>

			<td class="center">3.1</td>
			<td class="center">C</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>KHTML</td>
			<td>Konqureror 3.3</td>
			<td>KDE 3.3</td>

			<td class="center">3.3</td>
			<td class="center">A</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>KHTML</td>
			<td>Konqureror 3.5</td>
			<td>KDE 3.5</td>

			<td class="center">3.5</td>
			<td class="center">A</td>
		</tr>
		<tr class="odd_gradeX" id="2">
			<td>Tasman</td>
			<td>Internet Explorer 4.5</td>
			<td>Mac OS 8-9</td>

			<td class="center">-</td>
			<td class="center">X</td>
		</tr>
		<tr class="even_gradeC" id="4">
			<td>Tasman</td>
			<td>Internet Explorer 5.1</td>
			<td>Mac OS 7.6-9</td>

			<td class="center">1</td>
			<td class="center">C</td>
		</tr>
		<tr class="odd_gradeC" id="3">
			<td>Tasman</td>
			<td>Internet Explorer 5.2</td>
			<td>Mac OS 8-X</td>

			<td class="center">1</td>
			<td class="center">C</td>
		</tr>
		<tr class="even_gradeA" id="1">
			<td>Misc</td>
			<td>NetFront 3.1</td>
			<td>Embedded devices</td>

			<td class="center">-</td>
			<td class="center">C</td>
		</tr>
		<tr class="odd_gradeA" id="5">
			<td>Misc</td>
			<td>NetFront 3.4</td>
			<td>Embedded devices</td>

			<td class="center">-</td>
			<td class="center">A</td>
		</tr>
		<tr class="even_gradeX" id="11">
			<td>Misc</td>
			<td>Dillo 0.8</td>
			<td>Embedded devices</td>

			<td class="center">-</td>
			<td class="center">X</td>
		</tr>
		<tr class="odd_gradeX" id="2">
			<td>Misc</td>
			<td>Links</td>
			<td>Text only</td>

			<td class="center">-</td>
			<td class="center">X</td>
		</tr>
		<tr class="even_gradeX" id="11">
			<td>Misc</td>
			<td>Lynx</td>
			<td>Text only</td>

			<td class="center">-</td>
			<td class="center">X</td>
		</tr>
		<tr class="odd_gradeC" id="3">
			<td>Misc</td>
			<td>IE Mobile</td>
			<td>Windows Mobile 6</td>

			<td class="center">-</td>
			<td class="center">C</td>
		</tr>
		<tr class="even_gradeC" id="4">
			<td>Misc</td>
			<td>PSP browser</td>
			<td>PSP</td>

			<td class="center">-</td>
			<td class="center">C</td>
		</tr>
		<tr class="odd_gradeU" id="10">
			<td>Other browsers</td>
			<td>All others</td>
			<td>-</td>

			<td class="center">-</td>
			<td class="center">U</td>
		</tr>
		 -->
		
	</tbody>
</table>

			</div>


</div>

</body>
</html>
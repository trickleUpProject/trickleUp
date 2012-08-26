<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name . ' - Reports';
$this->breadcrumbs=array(
	'Reports',
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" src="jquery-1.8.0.min.js"></script>

<script>
activeDataId = 0;
function selectData(id)
{
	document.getElementById("data"+activeDataId).classList.remove("selectedMenuItem");
    document.getElementById("data"+activeDataId).classList.add("unselectedMenuItem");

    document.getElementById("data"+id).classList.remove("unselectedMenuItem");
	document.getElementById("data"+id).classList.add("selectedMenuItem");

	activeDataId = id;
	
	//alert("select data" + id);
	
	//UPDATE GRAPH
    redraw();
	
}

selectData(0);
</script>

        <script type="text/javascript" src=
            "https://www.google.com/jsapi"></script>
        <script type="text/javascript">

            google.load('visualization', '1.0', {'packages':['corechart']});
            google.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ["Month", "USA", "ABC", "Average"],
                    ["03", 112, 118, 115],
                    ["04", 212, 312, 262],
                    ["05", 200, 100, 150]
                ]);
            var options = {
                title:"Stuff Over Time",
                width:600,
                height:350,
                vAxis:{title:"Stuff"},
                hAxis:{title:"Time"},
                seriesType:"bars",
                series:{2:{type:"line"}}
            };
            var chart = new google.visualization.ComboChart(
                document.getElementById('chart_div'));
            chart.draw(data, options);
            }

            function redraw() {
                var data = google.visualization.arrayToDataTable([
                    ["Month", "USA", "ABC", "Average"],
                    ["03", 12, 18, 15],
                    ["04", 12, 12, 62],
                    ["05", 10, 10, 50]
                ]);
            var options = {
                title:"Stuff Over Time",
                width:600,
                height:350,
                vAxis:{title:"Stuff"},
                hAxis:{title:"Time"},
                seriesType:"bars",
                series:{2:{type:"line"}}
            };
            var chart = new google.visualization.ComboChart(
                document.getElementById('chart_div'));
            chart.draw(data, options);
            }
                

        </script>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
<div class="logo">
<img src="images/logo_trickleup.gif" width="151" height="35" alt="TrickleUp" />
</div>
<table width="100%" id="fullheight">
  <tr>
<td width="25%" class="leftColumn" valign="top"><H2>METRIC</H2>
  <p onclick="selectData(0)" id="data0" class="selectedMenuItem">Crop Investement</p>
  <p onclick="selectData(1)" id="data1" class="unselectedMenuItem">Asset Investement</p>
  <p onclick="selectData(2)" id="data2" class="unselectedMenuItem">Working Capital</p>
  <p onclick="selectData(3)" id="data3" class="unselectedMenuItem">Return on Investement</p>
  <p onclick="selectData(4)" id="data4" class="unselectedMenuItem">Livestock Mortality Rate</p>
</td>
<td class="rightColumn">
  During:
  <select>
    <option>This quarter</option>
    <option>Last quarter</option>
    <option>Last 3 quarters</option>
    <option>This year</option>
    <option>Last Year</option>
    <option>All time</option>
    </select>
  
  
  Filter by: 
  <input type="radio" name="group1" value="pp"> 
  Participants
  <input type="radio" name="group1" value="staff" checked> Staff<br>
   <hr>
   
   <div id="chart_div">
   
   </div>
   
   </td>
</tr>
</table>
</body>
</html>

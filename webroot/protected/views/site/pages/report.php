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
	if(id == 4){
    	drawMortalityLine();
	}
	else if(id == 0){
		drawChart();	
	}
	
}

selectData(0);
</script>

        <script type="text/javascript" src=
            "https://www.google.com/jsapi"></script>
        <script type="text/javascript">

            google.load('visualization', '1.0', {'packages':['corechart']});
			  google.load("visualization", "1", {packages: ['annotatedtimeline']});

            google.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ["Quarter", "Corn Flower", "Cucumber", "Goat/Sheep", "Paddy", "Pig", "Pisciculture", "Spinach"],
                    ["Q1", 4000, 2000, 7000, 4000, 2000, 7000, 7000],
                    ["Q2", 600, 2500, 1000, 4000, 2000, 7000, 4000],
                    ["Q3", 600, 2500, 1000, 5000, 2500, 2600, 5000], 
					["Q4", 5000, 2500, 2600, 4000, 2000, 7000, 4000]
                ]);
            var options = {
                title:"",
                width:700,
                height:350,
                vAxis:{title:"Input Cost (in Rs.)"},
                hAxis:{title:"Quarter"},
                seriesType:"bars",
              //  series:{2:{type:"line"}}
            };
            var chart = new google.visualization.ComboChart(
                document.getElementById('chart_div'));
            chart.draw(data, options);
			
					//enable during dropdown
					  document.getElementById('during').style.visibility='visible';  

            }

//
            function drawMortality() {
                var data = google.visualization.arrayToDataTable([
                    ["Quarter", "Cow/Sheep Mortality", "Pig Mortality"],
                    ["Q1", 12, 18],
                    ["Q2", 12, 12],
                    ["Q3", 10, 10],
					["Q3", 5, 8]
                ]);
            var options = {
                title:"",
                width:600,
                height:350,
                vAxis:{title:"Mortality Rate %"},
                hAxis:{title:"Quarter"},
                seriesType:"bars",
                series:{2:{type:"line"}}
            };
            var chart = new google.visualization.ComboChart(
                document.getElementById('chart_div'));
            chart.draw(data, options);
			
			
            }
	//		
	function drawMortalityLine() {
        var data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        data.addColumn('number', 'Sheep/Cow Mortality');
        data.addColumn('number', 'Pig Deaths');
        data.addRows([
          [new Date(2008, 1 ,1), .04, .08],
          [new Date(2008, 2 ,2), .02, .05],
          [new Date(2008, 3 ,3), .10, .12],
          [new Date(2008, 4 ,4), .03, .05],
          [new Date(2008, 5 ,5), .05, .06],
          [new Date(2008, 6 ,6), .08, .02]
        ]);
		
		//trying to formate numbers as percentages here... but not working...
		var formatter = new google.visualization.NumberFormat({ 
		  pattern: '#%', 
		  fractionDigits: 2
		});
		formatter.format(data, 2); // Apply formatter to first column.

        var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
        chart.draw(data, {displayAnnotations: true});
		
		//disable during dropdown
		  document.getElementById('during').style.visibility='hidden';  

		
      }
                

        </script>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
<div class="logo">
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

<form name="report" action=''> 
<td class="rightColumn"> <strong>During:</strong>
<select id="during">
  <option>This quarter</option>
  <option>Last quarter</option>
  <option>This year</option>
  <option>Last Year</option>
  <option>All time</option>
</select>
  </div>
  <strong>Filter by:</strong>
  <select>
  <option>Participants</option>
    <option>Staff</option>

    </select> | <strong>Individual:</strong>
    <select>
      <option>VIEW ALL</option>
    </select>

    <input type='button' value='View' onclick="test()"/>
</form>
  
   <hr>
   
   <div id="chart_div" style='width: 700px; height: 350px;'>
   
   </div>
 
   </td>
</tr>
</table>
</body>
</html>

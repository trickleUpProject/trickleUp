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
        data.addColumn('number', 'Pig Mortality');
        data.addColumn('number', 'Sheep/Goat Mortality');
        <?php 

$db=mysql_connect('localhost', 'root', 'root') or die('Could not connect');
mysql_select_db('trickleup', $db) or die('could not get to db');

$result = mysql_query("select * from (select  year, month ,count(*)  pig from trickleup.livestock_tracking where death is not null and livestock_type = 'pig' group by year, month ,livestock_type ) as g, (select  year, month ,count(*) goat from trickleup.livestock_tracking where death is not null and livestock_type = 'goat/sheep' group by year, month ,livestock_type) as p where g.year = p.year and g.month = p.month;") or die('Could not query');

if(mysql_num_rows($result)){

 while($row=mysql_fetch_row($result)){
?>
        
        data.addRow([new Date(<?php echo $row[0]; ?>, <?php echo $row[1]; ?> ,1), <?php echo $row[2]; ?>, <?php echo $row[5]; ?>]);
<?php
}

}



mysql_close($db);

?>		
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
  <option value='1'>This quarter</option>
  <option value='2'>Last quarter</option>
  <option value='4'>This year</option>
  <option value='8'>Last Year</option>
  <option value='all'>All time</option>
</select>
  </div>
  <strong>Filter by:</strong>
  <select>
  <option>Participants</option>
    <option>Staff</option>

    </select> | <strong>Individual:</strong>
    <select>
      <option>VIEW ALL</option>

        <?php 
$db2=mysql_connect('localhost', 'root', 'root') or die('Could not connect');
mysql_select_db('trickleup', $db2) or die('could not get to db');

$result2 = mysql_query("select distinct business_number, participant_name from livestock_tracking;") or die('Could not query');

if(mysql_num_rows($result2)){

 while($row2=mysql_fetch_row($result2)){
   echo "<option value = ";
   echo $row2[0];
   echo ">";
   echo $row2[0];
   echo "-";
   echo $row2[1];
   echo "<option>";
  }
}
?>
        
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

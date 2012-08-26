<?php 

$db=mysql_connect('localhost', 'root', 'root') or die('Could not connect');
mysql_select_db('trickleup', $db) or die('could not get to db');

$result = mysql_query("SELECT business_number, participant_name from agri_payment") or die('Could not query');

if(mysql_num_rows($result)){
    $dataTable = array(
    'cols' => array(
         // each column needs an entry here, like this:
		 array('type' => 'string', 'label' => 'business number'),
         array('type' => 'string', 'label' => 'name')

    )
);
$row=mysql_fetch_assoc($result);
 while($row=mysql_fetch_row($result)){
  $dataTable['rows'][] = array(
        'c' => array (
             array('v' => $row[0]), 
             array('v' => $row[1])
         )
    );
}

}

$json = json_encode($dataTable);

mysql_close($db);

?>
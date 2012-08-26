<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<br/>
<a href="<?php echo "index.php?r=upload/uploadExcel" ?>">Upload an Excel-file</a>
<br/><br/>

<?php
    Yii::import('application.vendors.PHPExcel',true);
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    $objPHPExcel = $objReader->load("/Users/larrylefever/Downloads/TrickleUp/Format 3A-2-test.xlsx"); //$file --> your filepath and filename
    
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
    $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
    echo '<table style="border: solid 1px">' . "\n";
    for ($row = 2; $row <= $highestRow; ++$row) {
      echo '<tr>' . "\n";
      for ($col = 0; $col <= $highestColumnIndex; ++$col) {
        echo '<td style="border: solid 1px">' . $objWorksheet->getCellByColumnAndRow($col, $row)->getValue() . '</td>' . "\n";
      }
      echo '</tr>' . "\n";
    }
    echo '</table>' . "\n";
?>

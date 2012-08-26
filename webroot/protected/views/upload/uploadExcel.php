<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Upload Excel</title>
</head>
<body>
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
    
</body>
</html>
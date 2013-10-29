<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'operator_id'); ?>
		<?php echo $form->textField($model,'operator_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'employee_id'); ?>
		<?php echo $form->textField($model,'employee_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'employee_code'); ?>
		<?php echo $form->textField($model,'employee_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'fullname'); ?>
		<?php echo $form->textField($model,'fullname'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'orun_code'); ?>
		<?php echo $form->textField($model,'orun_code'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
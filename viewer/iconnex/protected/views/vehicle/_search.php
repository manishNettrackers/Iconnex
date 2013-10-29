<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'vehicle_id'); ?>
		<?php echo $form->textField($model,'vehicle_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'vehicle_code'); ?>
		<?php echo $form->textField($model,'vehicle_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'vehicle_type_id'); ?>
		<?php echo $form->textField($model,'vehicle_type_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'operator_id'); ?>
		<?php echo $form->textField($model,'operator_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'vehicle_reg'); ?>
		<?php echo $form->textField($model,'vehicle_reg'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'orun_code'); ?>
		<?php echo $form->textField($model,'orun_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'vetag_indicator'); ?>
		<?php echo $form->textField($model,'vetag_indicator'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'modem_addr'); ?>
		<?php echo $form->textField($model,'modem_addr'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_id'); ?>
		<?php echo $form->textField($model,'build_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'wheelchair_access'); ?>
		<?php echo $form->textField($model,'wheelchair_access'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
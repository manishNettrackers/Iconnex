<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'employee-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'operator_id'); ?>
		<?php echo $form->textField($model,'operator_id'); ?>
		<?php echo $form->error($model,'operator_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'employee_code'); ?>
		<?php echo $form->textField($model,'employee_code'); ?>
		<?php echo $form->error($model,'employee_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'fullname'); ?>
		<?php echo $form->textField($model,'fullname'); ?>
		<?php echo $form->error($model,'fullname'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'orun_code'); ?>
		<?php echo $form->textField($model,'orun_code'); ?>
		<?php echo $form->error($model,'orun_code'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
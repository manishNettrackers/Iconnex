

<div class="subform">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'unit-build-form',
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
		<?php echo $form->labelEx($model,'build_code'); ?>
		<?php echo $form->textField($model,'build_code',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'build_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'unit_type'); ?>
		<?php echo $form->textField($model,'unit_type',array('size'=>8,'maxlength'=>8)); ?>
		<?php echo $form->error($model,'unit_type'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'build_parent'); ?>
		<?php echo $form->textField($model,'build_parent'); ?>
		<?php echo $form->error($model,'build_parent'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'build_status'); ?>
		<?php echo $form->textField($model,'build_status',array('size'=>1,'maxlength'=>1)); ?>
		<?php echo $form->error($model,'build_status'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'version_id'); ?>
		<?php echo $form->textField($model,'version_id'); ?>
		<?php echo $form->error($model,'version_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'build_notes1'); ?>
		<?php echo $form->textField($model,'build_notes1',array('size'=>40,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'build_notes1'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'build_notes2'); ?>
		<?php echo $form->textField($model,'build_notes2',array('size'=>40,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'build_notes2'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'build_type'); ?>
		<?php echo $form->textField($model,'build_type',array('size'=>1,'maxlength'=>1)); ?>
		<?php echo $form->error($model,'build_type'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'allow_logs'); ?>
		<?php echo $form->textField($model,'allow_logs'); ?>
		<?php echo $form->error($model,'allow_logs'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'allow_publish'); ?>
		<?php echo $form->textField($model,'allow_publish'); ?>
		<?php echo $form->error($model,'allow_publish'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
        <br>
        <br>
		<?php echo CHtml::ajaxButton('Close', 'http://www.google.co.uk', array(), array ( 'name' => 'close-button', 'class' => 'close-button', 'id' => 'close-button')); ?>
	</div>
        <p>

<?php $this->endWidget(); ?>

</div><!-- form -->

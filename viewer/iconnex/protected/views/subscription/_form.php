<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'subscription-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'subscriber_id'); ?>
		<?php echo $form->textField($model,'subscriber_id'); ?>
		<?php echo $form->error($model,'subscriber_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'subscription_type'); ?>
		<?php echo $form->textField($model,'subscription_type',array('size'=>//10,'maxlength'=>//10)); ?>
		<?php echo $form->error($model,'subscription_type'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'creation_time'); ?>
		<?php echo $form->textField($model,'creation_time'); ?>
		<?php echo $form->error($model,'creation_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'start_time'); ?>
		<?php echo $form->textField($model,'start_time'); ?>
		<?php echo $form->error($model,'start_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'end_time'); ?>
		<?php echo $form->textField($model,'end_time'); ?>
		<?php echo $form->error($model,'end_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'subscribed_time'); ?>
		<?php echo $form->textField($model,'subscribed_time'); ?>
		<?php echo $form->error($model,'subscribed_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'update_interval'); ?>
		<?php echo $form->textField($model,'update_interval'); ?>
		<?php echo $form->error($model,'update_interval'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'max_departures'); ?>
		<?php echo $form->textField($model,'max_departures'); ?>
		<?php echo $form->error($model,'max_departures'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'display_thresh'); ?>
		<?php echo $form->textField($model,'display_thresh'); ?>
		<?php echo $form->error($model,'display_thresh'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'request_id'); ?>
		<?php echo $form->textField($model,'request_id'); ?>
		<?php echo $form->error($model,'request_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'disabled'); ?>
		<?php echo $form->textField($model,'disabled',array('size'=>//1,'maxlength'=>//1)); ?>
		<?php echo $form->error($model,'disabled'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'subscription_ref'); ?>
		<?php echo $form->textField($model,'subscription_ref',array('size'=>//64,'maxlength'=>//64)); ?>
		<?php echo $form->error($model,'subscription_ref'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
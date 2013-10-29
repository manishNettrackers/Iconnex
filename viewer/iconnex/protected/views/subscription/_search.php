<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'subscription_id'); ?>
		<?php echo $form->textField($model,'subscription_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subscriber_id'); ?>
		<?php echo $form->textField($model,'subscriber_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subscription_type'); ?>
		<?php echo $form->textField($model,'subscription_type',array('size'=>//10,'maxlength'=>//10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'creation_time'); ?>
		<?php echo $form->textField($model,'creation_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'start_time'); ?>
		<?php echo $form->textField($model,'start_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'end_time'); ?>
		<?php echo $form->textField($model,'end_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subscribed_time'); ?>
		<?php echo $form->textField($model,'subscribed_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'update_interval'); ?>
		<?php echo $form->textField($model,'update_interval'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'max_departures'); ?>
		<?php echo $form->textField($model,'max_departures'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'display_thresh'); ?>
		<?php echo $form->textField($model,'display_thresh'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'request_id'); ?>
		<?php echo $form->textField($model,'request_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'disabled'); ?>
		<?php echo $form->textField($model,'disabled',array('size'=>//1,'maxlength'=>//1)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subscription_ref'); ?>
		<?php echo $form->textField($model,'subscription_ref',array('size'=>//64,'maxlength'=>//64)); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
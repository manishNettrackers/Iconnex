<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'subscriber_id'); ?>
		<?php echo $form->textField($model,'subscriber_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subscriber_code'); ?>
		<?php echo $form->textField($model,'subscriber_code',array('size'=>//64,'maxlength'=>//64)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'user_id'); ?>
		<?php echo $form->textField($model,'user_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'ip_address'); ?>
		<?php echo $form->textField($model,'ip_address',array('size'=>//20,'maxlength'=>//20)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'gateway_id'); ?>
		<?php echo $form->textField($model,'gateway_id'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
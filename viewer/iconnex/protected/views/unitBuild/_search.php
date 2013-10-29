<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'build_id'); ?>
		<?php echo $form->textField($model,'build_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'operator_id'); ?>
		<?php echo $form->textField($model,'operator_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_code'); ?>
		<?php echo $form->textField($model,'build_code',array('size'=>//10,'maxlength'=>//10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'unit_type'); ?>
		<?php echo $form->textField($model,'unit_type',array('size'=>//8,'maxlength'=>//8)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>//20,'maxlength'=>//20)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_parent'); ?>
		<?php echo $form->textField($model,'build_parent'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_status'); ?>
		<?php echo $form->textField($model,'build_status',array('size'=>//1,'maxlength'=>//1)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'version_id'); ?>
		<?php echo $form->textField($model,'version_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_notes1'); ?>
		<?php echo $form->textField($model,'build_notes1',array('size'=>//40,'maxlength'=>//40)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_notes2'); ?>
		<?php echo $form->textField($model,'build_notes2',array('size'=>//40,'maxlength'=>//40)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'build_type'); ?>
		<?php echo $form->textField($model,'build_type',array('size'=>//1,'maxlength'=>//1)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'allow_logs'); ?>
		<?php echo $form->textField($model,'allow_logs'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'allow_publish'); ?>
		<?php echo $form->textField($model,'allow_publish'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
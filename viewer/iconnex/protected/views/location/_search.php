<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'location_id'); ?>
		<?php echo $form->textField($model,'location_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'location_code'); ?>
		<?php echo $form->textField($model,'location_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'gprs_xmit_code'); ?>
		<?php echo $form->textField($model,'gprs_xmit_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'point_type'); ?>
		<?php echo $form->textField($model,'point_type'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'route_area_id'); ?>
		<?php echo $form->textField($model,'route_area_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'description'); ?>
		<?php echo $form->textField($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'public_name'); ?>
		<?php echo $form->textField($model,'public_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'receive'); ?>
		<?php echo $form->textField($model,'receive'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'latitude_degrees'); ?>
		<?php echo $form->textField($model,'latitude_degrees'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'latitude_minutes'); ?>
		<?php echo $form->textField($model,'latitude_minutes'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'latitude_heading'); ?>
		<?php echo $form->textField($model,'latitude_heading'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'longitude_degrees'); ?>
		<?php echo $form->textField($model,'longitude_degrees'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'longitude_minutes'); ?>
		<?php echo $form->textField($model,'longitude_minutes'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'longitude_heading'); ?>
		<?php echo $form->textField($model,'longitude_heading'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'geofence_radius'); ?>
		<?php echo $form->textField($model,'geofence_radius'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'pass_angle'); ?>
		<?php echo $form->textField($model,'pass_angle'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'gazetteer_code'); ?>
		<?php echo $form->textField($model,'gazetteer_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'gazetteer_id'); ?>
		<?php echo $form->textField($model,'gazetteer_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'place_id'); ?>
		<?php echo $form->textField($model,'place_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'district_id'); ?>
		<?php echo $form->textField($model,'district_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'arriving_addon'); ?>
		<?php echo $form->textField($model,'arriving_addon'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'exit_addon'); ?>
		<?php echo $form->textField($model,'exit_addon'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'bay_no'); ?>
		<?php echo $form->textField($model,'bay_no'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
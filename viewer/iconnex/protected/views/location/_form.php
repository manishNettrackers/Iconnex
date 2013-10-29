<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'location-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'location_code'); ?>
		<?php echo $form->textField($model,'location_code'); ?>
		<?php echo $form->error($model,'location_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gprs_xmit_code'); ?>
		<?php echo $form->textField($model,'gprs_xmit_code'); ?>
		<?php echo $form->error($model,'gprs_xmit_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'point_type'); ?>
		<?php echo $form->textField($model,'point_type'); ?>
		<?php echo $form->error($model,'point_type'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'route_area_id'); ?>
		<?php echo $form->textField($model,'route_area_id'); ?>
		<?php echo $form->error($model,'route_area_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description'); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'public_name'); ?>
		<?php echo $form->textField($model,'public_name'); ?>
		<?php echo $form->error($model,'public_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'receive'); ?>
		<?php echo $form->textField($model,'receive'); ?>
		<?php echo $form->error($model,'receive'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'latitude_degrees'); ?>
		<?php echo $form->textField($model,'latitude_degrees'); ?>
		<?php echo $form->error($model,'latitude_degrees'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'latitude_minutes'); ?>
		<?php echo $form->textField($model,'latitude_minutes'); ?>
		<?php echo $form->error($model,'latitude_minutes'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'latitude_heading'); ?>
		<?php echo $form->textField($model,'latitude_heading'); ?>
		<?php echo $form->error($model,'latitude_heading'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'longitude_degrees'); ?>
		<?php echo $form->textField($model,'longitude_degrees'); ?>
		<?php echo $form->error($model,'longitude_degrees'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'longitude_minutes'); ?>
		<?php echo $form->textField($model,'longitude_minutes'); ?>
		<?php echo $form->error($model,'longitude_minutes'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'longitude_heading'); ?>
		<?php echo $form->textField($model,'longitude_heading'); ?>
		<?php echo $form->error($model,'longitude_heading'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'geofence_radius'); ?>
		<?php echo $form->textField($model,'geofence_radius'); ?>
		<?php echo $form->error($model,'geofence_radius'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'pass_angle'); ?>
		<?php echo $form->textField($model,'pass_angle'); ?>
		<?php echo $form->error($model,'pass_angle'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gazetteer_code'); ?>
		<?php echo $form->textField($model,'gazetteer_code'); ?>
		<?php echo $form->error($model,'gazetteer_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gazetteer_id'); ?>
		<?php echo $form->textField($model,'gazetteer_id'); ?>
		<?php echo $form->error($model,'gazetteer_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'place_id'); ?>
		<?php echo $form->textField($model,'place_id'); ?>
		<?php echo $form->error($model,'place_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'district_id'); ?>
		<?php echo $form->textField($model,'district_id'); ?>
		<?php echo $form->error($model,'district_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'arriving_addon'); ?>
		<?php echo $form->textField($model,'arriving_addon'); ?>
		<?php echo $form->error($model,'arriving_addon'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'exit_addon'); ?>
		<?php echo $form->textField($model,'exit_addon'); ?>
		<?php echo $form->error($model,'exit_addon'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'bay_no'); ?>
		<?php echo $form->textField($model,'bay_no'); ?>
		<?php echo $form->error($model,'bay_no'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
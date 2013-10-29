<div class="subform">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'vehicle-form',
	'enableAjaxValidation'=>true,
)); ?>


	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
    <?php echo $form->labelEx($model,'vehicle_type_id'); ?>
    <?php echo $form->dropDownList($model, 'vehicle_type_id', CHtml::listData(
    VehicleType::model()->findAll(), 'vehicle_type_id', 'vehicle_type_code'),
    array('prompt' => 'Select a Vehicle Type')
    ); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'vehicle_code'); ?>
		<?php echo $form->textField($model,'vehicle_code'); ?>
		<?php echo $form->error($model,'vehicle_code'); ?>
	</div>

	<div class="row">
		<!--?php echo $form->labelEx($model,'vehicle_type_id'); ?-->
		<!--?php echo $form->textField($model,'vehicle_type_id'); ?-->
		<!--?php echo $form->error($model,'vehicle_type_id'); ?-->
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'operator_id'); ?>
		<?php echo $form->textField($model,'operator_id'); ?>
		<?php echo $form->error($model,'operator_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'vehicle_reg'); ?>
		<?php echo $form->textField($model,'vehicle_reg'); ?>
		<?php echo $form->error($model,'vehicle_reg'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'orun_code'); ?>
		<?php echo $form->textField($model,'orun_code'); ?>
		<?php echo $form->error($model,'orun_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'vetag_indicator'); ?>
		<?php echo $form->textField($model,'vetag_indicator'); ?>
		<?php echo $form->error($model,'vetag_indicator'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'modem_addr'); ?>
		<?php echo $form->textField($model,'modem_addr'); ?>
		<?php echo $form->error($model,'modem_addr'); ?>
	</div>

	<div class="row">
    <?php echo $form->labelEx($model,'build_id'); ?>
    <?php echo $form->dropDownList($model, 'build_id', CHtml::listData(
    UnitBuild::model()->findAll("unit_type = 'BUS' AND build_id NOT IN ( select vehicle.build_id FROM vehicle )"), 'build_id', 'build_code'),
    array('prompt' => 'Select an inventory OVS to be installed on this vehicle')
    ); ?>
	</div>


	<div class="row">
		<?php echo $form->labelEx($model,'wheelchair_access'); ?>
         <?php echo $form->checkBox($model,'wheelchair_access'); ?>
		<?php echo $form->error($model,'wheelchair_access'); ?>
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

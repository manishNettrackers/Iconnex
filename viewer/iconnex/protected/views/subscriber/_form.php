<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'subscriber-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'subscriber_code'); ?>
		<?php echo $form->textField($model,'subscriber_code',array('size'=>64,'maxlength'=>64)); ?>
		<?php echo $form->error($model,'subscriber_code'); ?>
	</div>

<!--
	<div class="row">
		<?php echo $form->labelEx($model,'user_id'); ?>
		<?php echo $form->textField($model,'user_id'); ?>
		<?php echo $form->error($model,'user_id'); ?>
	</div>
-->
    <div class="row">
    <?php echo $form->labelEx($model,'user_id'); ?>
    <?php echo $form->dropDownList($model, 'user_id', CHtml::listData(
        CentUser::model()->findAll(), 'userid', 'usernm'),
    array('prompt' => 'Select a user')
    ); ?>
    </div>

	<div class="row">
		<?php echo $form->labelEx($model,'ip_address'); ?>
		<?php echo $form->textField($model,'ip_address',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'ip_address'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gateway_id'); ?>
		<?php echo $form->textField($model,'gateway_id'); ?>
		<?php echo $form->error($model,'gateway_id'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

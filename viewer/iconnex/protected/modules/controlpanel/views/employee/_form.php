<style type="text/css">
.errorMessage{ color:#E00; font-size:10px; }
</style>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'employee-form',
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'fullname'); ?>
		<?php echo $form->textField($model,'fullname'); ?>
		<div class="errorMessage" id="Employee_fullname_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'operator_id'); ?>
		<?php 
			$list = CHtml::listData(operator::model()->findAll(),'operator_id', 'operator_code');
			echo CHtml::dropDownList('operator_id', $model->operator_id, $list);
		?>
	</div>
	
	
<?php if(!$model->isNewRecord) { ?>
	<div class="row">
		<?php echo $form->labelEx($model,'employee_code'); ?>
		<?php echo $model->employee_code; ?>
	</div>
<?php } else {?>
	<div class="row">
		<?php echo $form->labelEx($model,'employee_code'); ?>
		<?php echo $form->textField($model,'employee_code'); ?>
		<div class="errorMessage" id="Employee_employee_code_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
	</div>

	
	<?php } ?>

	<div class="row">
		<?php echo $form->labelEx($model,'orun_code'); ?>
		<?php echo $form->textField($model,'orun_code'); ?>
	</div>

	<div class="row buttons">
		<input type="hidden" id="url" value="<?php echo $model->isNewRecord ? 'Employee/create': 'Employee/update&id='.$_REQUEST['id'];?>" />
		<input type="hidden" id="action" value="<?php echo $model->isNewRecord ? 'create' : 'update';?>" />
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
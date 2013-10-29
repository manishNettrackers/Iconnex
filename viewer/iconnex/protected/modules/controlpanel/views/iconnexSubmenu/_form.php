<style type="text/css">
.errorMessage{ color:#E00; font-size:10px; }


</style>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'iconnex-submenu-form',
	//'enableAjaxValidation'=>true,
	'clientOptions'=>array('validateOnSubmit'=>true),
)); ?>

<?php echo $form->errorSummary($model); ?>
<div style="float:left; width:50%;">
<div class="row">
		<?php echo $form->labelEx($model,'app_name'); ?>
		<?php echo $form->textField($model,'app_name',array('size'=>60,'maxlength'=>60)); ?>
		<div class="errorMessage" id="IconnexSubmenu_app_name_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'app_url'); ?>
		<?php echo $form->textField($model,'app_url',array('size'=>60,'maxlength'=>255)); ?>
		<div class="errorMessage" id="IconnexSubmenu_app_url_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
		
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'refresh_xml'); ?>
		<?php echo $form->textField($model,'refresh_xml',array('size'=>40,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'refresh_xml'); ?>
		
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'has_map'); ?>
		<?php echo $form->checkBox($model,'has_map'); ?>
		<?php echo $form->error($model,'has_map'); ?>
		
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'has_grid'); ?>
		<?php echo $form->checkBox($model,'has_grid'); ?>
		<?php echo $form->error($model,'has_grid'); ?>
		
	</div>
</div>
<div style="float:right;width:50%;">
	

	<div class="row">
		<?php echo $form->labelEx($model,'has_line'); ?>
		<?php echo $form->checkBox($model,'has_line'); ?>
		<?php echo $form->error($model,'has_line'); ?>
		
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'has_chart'); ?>
		<?php echo $form->checkBox($model,'has_chart'); ?>
		<?php echo $form->error($model,'has_chart'); ?>
		
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'has_report'); ?>
		<?php echo $form->checkBox($model,'has_report'); ?>
		<?php echo $form->error($model,'has_report'); ?>
		
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'autorun'); ?>
		<?php echo $form->checkBox($model,'autorun'); ?>
		<?php echo $form->error($model,'autorun'); ?>
		
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'autorefresh'); ?>
		<?php echo $form->checkBox($model,'autorefresh'); ?>
		<?php echo $form->error($model,'autorefresh'); ?>
		
	</div>

</div>

<div style="clear:both;"></div>
	
	
	<div class="row buttons">
		<input type="hidden" value="<?php echo $_REQUEST['menu_id'];?>" />
		<input type="hidden" id="url" value="<?php echo $model->isNewRecord ? 'IconnexSubmenu/create&menu_id='.$_REQUEST['menu_id'] : 'IconnexSubmenu/update&id='.$_REQUEST['id'].'&menu_id='.$_REQUEST['menu_id'] ;?>" />
		<input type="hidden" id="action" value="<?php echo $model->isNewRecord ? 'create' : 'update';?>" />
		
		
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

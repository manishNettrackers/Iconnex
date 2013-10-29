<style type="text/css">
button.close {
    padding: 0;
    cursor: pointer;
    background: transparent;
    border: 0;
    -webkit-appearance: none;
}

.btn {
    display: inline-block;
    *display: inline;
    padding: 4px 14px;
    margin-bottom: 0;
    *margin-left: .3em;
    font-size: 14px;
    line-height: 20px;
    *line-height: 20px;
    color: #333333;
    text-align: center;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
    vertical-align: middle;
    cursor: pointer;
    background-color: #f5f5f5;
    *background-color: #e6e6e6;
    background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), to(#e6e6e6));
    background-image: -webkit-linear-gradient(top, #ffffff, #e6e6e6);
    background-image: -o-linear-gradient(top, #ffffff, #e6e6e6);
    background-image: linear-gradient(to bottom, #ffffff, #e6e6e6);
    background-image: -moz-linear-gradient(top, #ffffff, #e6e6e6);
    background-repeat: repeat-x;
    border: 1px solid #bbbbbb;
    *border: 0;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    border-color: #e6e6e6 #e6e6e6 #bfbfbf;
    border-bottom-color: #a2a2a2;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    filter: progid:dximagetransform.microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffe6e6e6', GradientType=0);
    filter: progid:dximagetransform.microsoft.gradient(enabled=false);
    *zoom: 1;
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
}


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
		<div class="errorMessage" id="IconnexSubmenu_app_name"></div>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'app_url'); ?>
		<?php echo $form->textField($model,'app_url',array('size'=>60,'maxlength'=>255)); ?>
		<div class="errorMessage" id="IconnexSubmenu_app_url"></div>
		
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
		<input type="button" class="btn" onclick="savedata('<?php echo $model->isNewRecord ? 'IconnexSubmenu/create&menu_id='.$_REQUEST['menu_id'] : 'IconnexSubmenu/update&id='.$_REQUEST['id'].'&menu_id='.$_REQUEST['menu_id'] ;?>','<?php echo $model->isNewRecord ? 'create' : 'update';?>')" value="<?php echo $model->isNewRecord ? 'Create' : 'Save';?>" />
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

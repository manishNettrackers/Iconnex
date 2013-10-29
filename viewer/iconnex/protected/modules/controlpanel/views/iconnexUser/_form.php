<style type="text/css">

.errorMessage{ color:#E00; font-size:10px; }


</style>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'iconnex-user-form',
)); ?>


	<?php echo $form->errorSummary($model); ?>
	<div style="float:left; width:50%;">
		<div class="row">
			<?php echo $form->labelEx($model,'usernm'); ?>
			<?php echo $form->textField($model,'usernm',array('size'=>15,'maxlength'=>15)); ?>
			<div class="errorMessage" id="IconnexUser_usernm_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
		</div>
	
		<div class="row">
			<?php echo $form->labelEx($model,'narrtv'); ?>
			<?php echo $form->textField($model,'narrtv',array('size'=>20,'maxlength'=>20)); ?>
			<?php echo $form->error($model,'narrtv'); ?>
			
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'passwd_md5'); ?>
			<?php echo $form->passwordField($model,'passwd_md5',array('size'=>40,'maxlength'=>40,'value'=>'')); ?>
			<div class="errorMessage" id="IconnexUser_passwd_md5_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
		</div>
	
		<div class="row">
			<?php echo $form->labelEx($model,'emailad'); ?>
			<?php echo $form->textField($model,'emailad',array('size'=>30,'maxlength'=>200)); ?>
			<div class="errorMessage" id="IconnexUser_emailad_<?php echo $model->isNewRecord ? 'create' : 'update';?>"></div>
		</div>
	</div>
	<div style="float:right; width:50%;">
		<div class="row">
		<label> Assigned menu to user</label>
			<?php
				
				echo $form->dropDownList($model_new,'menu_id', CHtml::listData(iconnexMenu::model()->findAll(), 'menu_id', 'menu_name') ,
				 array(
				 		 'prompt'=>'Select the menu',
						 'multiple' => 'true',
						 'style'=>'width:230px; height:230px;',
						 'options'=>$mappingmenu,
					  ));
			?>
			</div>
	</div>
	<div style="clear:both;"></div>
	

	<div class="row buttons">
	<input type="hidden" id="url" value="<?php echo $model->isNewRecord ? 'IconnexUser/create' : 'IconnexUser/update&id='.$_REQUEST['id'];?>" />
	<input type="hidden" id="action" value="<?php echo $model->isNewRecord ? 'create' : 'update';?>" />
	
	
	
		<!--<input type="button" class="btn" onclick="savedata('<?php //echo $model->isNewRecord ? 'IconnexUser/create' : 'IconnexUser/update&id='.$_REQUEST['id'];?>','<?php //echo $model->isNewRecord ? 'create' : 'update';?>')" value="<?php //echo $model->isNewRecord ? 'Create' : 'Save';?>" />-->
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
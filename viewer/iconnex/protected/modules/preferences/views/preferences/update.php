<div class="container1">
<div class="siglepage">
<div class="form formfield1">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'user-form',
	'enableAjaxValidation'=>true,
)); ?>
<div class="flash-success" style="display:<?php echo (Yii::app()->user->hasFlash("User")?"":"none") ?>;">

<?php 

if(Yii::app()->user->hasFlash("User"))
	{
		 foreach(Yii::app()->user->getFlash("User") as $ls_errorKey => $la_errorMessage)
		 {
			echo  $la_errorMessage . "<br>";
		
		 }
	}
	

?>
	</div>
   
	<?php echo $form->errorSummary($model); ?>
	<div class="row">
		<?php echo $form->labelEx($model,'usernm'); ?>
		<?php echo $form->textField($model,'usernm',array('size'=>40,'maxlength'=>255, 'class'=>'textfield1')); ?>
		<?php echo $form->error($model,'usernm'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'emailad'); ?>
		<?php echo $form->textField($model,'emailad',array('size'=>40,'maxlength'=>255, 'class'=>'textfield1')); ?>
		<?php echo $form->error($model,'emailad'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'passwd_md5'); ?>
		<?php echo $form->passwordField($model,'passwd_md5',array('size'=>40,'maxlength'=>255,'value'=>'', 'class'=>'textfield1')); ?>
		<?php echo $form->error($model,'passwd_md5'); ?>
	</div>
	
	<div class="row buttons">
    	<label>&nbsp;</label>
		<?php echo CHtml::submitButton('Update',array('class'=>'exclusive')); ?>
	</div>

<?php $this->endWidget(); ?>
</div>
</div>
</div>
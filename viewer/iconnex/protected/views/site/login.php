<h2>Log In</h2>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableAjaxValidation'=>false,
)); ?>
  <fieldset>
    <p>
      <?php echo $form->textField($model,'username',array("placeholder"=>"User ID" ,"class"=>"textfield1")); ?>
      <?php echo $form->error($model,'username'); ?>
    </p>
    <p>
      <?php echo $form->passwordField($model,'password',array("placeholder"=>"Password" ,"class"=>"textfield1 password")); ?>
      <?php echo $form->error($model,'password'); ?>
    </p>
    <p class="check">
      <?php echo $form->checkBox($model,'rememberMe'); ?>
      <label for="remember">Remember Me</label>
      <span>|</span><a href="#">Forgot Password</a></p>
    <p class="button_block">
      <?php echo CHtml::submitButton('Login',array('class'=>'button')); ?>
    </p>
  </fieldset>
<?php $this->endWidget(); ?>

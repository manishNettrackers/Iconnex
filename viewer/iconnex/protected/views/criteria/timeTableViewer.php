<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'timetableviewercriteria',
        ));
?>
<div 
    <div>
            <?php echo $form->labelEx($model, 'operator'); ?>
            <?php echo $form->dropdownlist($model, 'operator_code', CHtml::listData(operator::model()->findAll(), 'operator_id', 'operator_code'), array('prompt' => 'Select', 'class' => 'field')); ?>
            <?php echo $form->error($model, 'operator'); ?>
    </div> 
    <br>
    <div>
        <?php echo $form->labelEx($route, 'route_id'); ?>
        <?php echo $form->dropdownlist($route, 'route_code', CHtml::listData(Route::model()->findAll(), 'route_id', 'route_code'), array('prompt' => 'Select', 'class' => 'field')); ?>
        <?php echo $form->error($route, 'route_id'); ?>
    </div>
    <br>
    <div>
        <?php echo $form->labelEx($route, 'dateFrom'); ?>
        <?php echo CHtml::textField('User[daterangefrom]', date('Y-m-d'), array('class' => 'field', 'size' => 10, 'maxlength' => 128)); ?>                
        <?php echo $form->labelEx($route, 'dateTo'); ?>
        <?php echo CHtml::textField('User[daterangeto]', date('Y-m-d'), array('class' => 'field', 'size' => 10, 'maxlength' => 128)); ?> 

    </div>
    <br>
    <div>
        <?php echo $form->labelEx($route, 'timeFrom'); ?>
        <?php echo CHtml::textField('User[timerangefrom]',  '00:00:00', array('class' => 'field', 'size' => 10, 'maxlength' => 128)); ?>                
        <?php echo $form->labelEx($route, 'timeTo'); ?>
        <?php echo CHtml::textField('User[timerangeto]', '23:59:59', array('class' => 'field', 'size' => 10, 'maxlength' => 128)); ?> 

    </div>
    <br>
    <div>
        <?php echo $form->labelEx($route, 'Duty Number'); ?>
        <?php echo CHtml::textField('User[dutynumber]','', array('class' => 'field', 'size' => 20, 'maxlength' => 128)); ?>                
    </div> 
    <br>
    <div>
        <?php echo $form->labelEx($route, 'Runing Board'); ?>
        <?php echo CHtml::textField('User[runingboard]','', array('class' => 'field', 'size' => 20, 'maxlength' => 128)); ?>                
    </div>
    <br/>
    <div>
        <?php //echo CHtml::submitButton('update', array('id' => 'update')); ?>
        <a href ="#" id ="timetableviewercriteriabutton">Submit</a>
    </div>
    <?php $this->endWidget(); ?> 

    <script>
    
        /* get the post element object */
        var parentelementobject  = <?php echo(json_encode($_POST['parentelementobject'])); ?>;
    </script>


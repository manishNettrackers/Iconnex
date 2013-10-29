<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/driver.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
<div class="container" id="page">

	<div id="header">

	<div id="rgbmainmenu" style="width: 100%">
		<div id="rgblogo">
        Driving Performance<br>
		<?php
            echo CHtml::link('Logout',array('/site/logout'), array('style'=>'color:#ffffff;font-size:14pt;text-decoration: none;text-align: left;'));
		?>
        </div>
		<div id="rgbdriver"></div>
		<div id="rgbnavmenudiv">
		<?php
			//$this->widget('application.extensions.topmenu.TopMenu',array(
            //'id'=>'rgbnavmenu',
			//'items'=>array(
				//array('label'=>'Home', 'url'=>array('/site/index'), 'view'=>'mainmenu'),
				//array('label'=>'PWI', 'url'=>array('/pwi/pwi'), 'active'=>Yii::app()->controller->id=='pwi'),
				//array('label'=>'iConnex', 'url'=>array('/infohost/index','view'=>'mainmenu'), 'visible'=>Yii::app()->user->allowedAccess('task', 'Use iConnex')),
				//array('label'=>'Analytics', 'url'=>array('/golap/golap'), 'visible'=>Yii::app()->user->allowedAccess('task', 'Use ODS'), 'active'=>Yii::app()->controller->id=='golap'),
				//array('label'=>'Login', 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
				//array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest)
			//),
		//)); ?>
        </div>
		<div id="rgblogoright" style="width:30%; float:right; text-align: left">&nbsp;
        </div>
	</div><!-- mainmenu -->

	</div><!-- header -->

<!--
	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
	)); ?>--><!-- breadcrumbs -->

	<?php echo $content; ?>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> Connexionz UK.
		All Rights Reserved<br/>
		<?php //echo Yii::powered(); ?>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>

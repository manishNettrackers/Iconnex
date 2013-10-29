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
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/rbc.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>

<div class="container" style="width: 100%" id="page">

	<div id="headerrbc">
	    <div id="headerleftrbc">
		<div id="sitetitle">iConnex<i>Web</i></div>
	    <div id="mainmenurbc">
		<?php
            $menuwidget = "application.extensions.rbcmenu.RbcMenu";
            include ("mainmenuitems.php");
		?>
	    </div><!-- mainmenu -->
	    </div><!-- mainmenu -->
		<div id="companylogoright">&nbsp;</div>
	</div><!-- header -->


<!--
	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
	)); ?>--><!-- breadcrumbs -->

    <div id="contentrbc">
	<?php echo $content; ?>
    </div>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> Connexionz UK.
		All Rights Reserved
		<?php //echo Yii::powered(); ?>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>

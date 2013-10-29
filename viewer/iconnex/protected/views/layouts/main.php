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

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/anyuser.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
<!--	<link rel="stylesheet" type="text/css" href="<?php //echo Yii::app()->request->baseUrl; ?>/css/style.css" />
	<script type="text/javascript" src="<?php //echo Yii::app()->request->baseUrl; ?>/js/jquery.min.js"></script>-->
	<?php		
		Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/bootstrap/bootstrap-editable.css');
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/bootstrap/bootstrap-editable.js');
    ?> 

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>

<div class="container" id="page">

	<div id="header">
	<?php if(!Yii::app()->user->isGuest) {?>
		<div class="profile_link forblack">
			<a class="profile_img" href="javascript:void(0)" id="click_pro">
				<img src="<?php echo Yii::app()->request->baseUrl; ?>/images/profile.jpg" />
				<span><?php echo Yii::app()->user->name;?></span>
			</a>
			<div class="open_div"> 
				<div class="link">
					<p><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=preferences/preferences/preferences">Account </a></p>
					<p><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=/site/logout">Logout </a></p>
				</div>
			</div>
		</div>
		
	<?php } ?>
		<div id="logoright" style="float:right"><i>iConnex Web</i></div>
	</div><!-- header -->

    <div id="mainmenu">
		    <?php  
                    
                $menuwidget = "application.extensions.topmenu.TopMenu";
                include("mainmenuitems.php");
		    ?>
	</div>
    <!-- mainmenu -->



	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
	)); ?><!----><!-- breadcrumbs -->

    <div id="content">
	<?php echo $content; 
        
        ?>
    </div>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> Connexionz UK.
	here	All Rights Reserved<br/>
		<?php //echo Yii::powered(); ?>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>
<script type="text/javascript">
$('#click_pro').click(function(e) {
	$('.open_div').slideDown(100);
	$(this).addClass("active");
	e.stopPropagation();
})
$(function(){
	$(document).click(function(){  
		$('.open_div').slideUp(100);
		$("#click_pro").removeClass("active");
	});
});
</script>
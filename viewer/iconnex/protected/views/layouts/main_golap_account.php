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
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/newcss/responsive.css" />
    <?php 
	Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/bootstrap/bootstrap-editable.css');
	Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/bootstrap/bootstrap-editable.js');
	Yii::app()->clientScript->registerCoreScript('jquery'); ?>

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
     <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/newjs/jquery.popupoverlay.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/newjs/global.js"></script>
</head>
<body>

<div class="container" style="width: 100%; height: 100%; margin:  0 auto -44px;" id="page">
	<header>
    <aside class="logo"><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=/golap/golap">iConnex<i>Web</i></a></aside>
    <aside class="rightHeader">
      <div class="myaccount">
        <div class="user"><a href="javascript:void" id="click_pro"><img src="newimages/profile.jpg" /> <span><?php echo ucfirst(Yii::app()->user->name);?></span></a></div>
        <div class="submenu open_div">
          <ul>
            <li><a href="<?php echo Yii::app()->request->baseUrl;?>/index.php?r=preferences/preferences/preferences">Account </a></li>
            <?php if(ucfirst(Yii::app()->user->name)=='Admin'){?>
            	<li><a href="<?php echo Yii::app()->request->baseUrl;?>/index.php?r=controlpanel/IconnexMenu/menu">Menu </a></li>
            	<li><a href="<?php echo Yii::app()->request->baseUrl;?>/index.php?r=controlpanel/IconnexUser/user">User </a></li>
            <?php }?>
            <li><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=/site/logout">Logout </a></li>
          </ul>
        </div>
      </div>
      <script type="text/javascript">
				$('#click_pro').click(function(e) {
					$('.open_div').slideDown(200); 
					$(this).addClass("active"); 
					e.stopPropagation();
				})
				$(document).click(function(){  
					$('.open_div').slideUp(200); 
					$('#click_pro').removeClass("active"); 
				});
			</script> 
    </aside>
  </header>
   <nav class="navigation">
    <ul class="left">
      <li><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=/golap/golap" class="home"></a></li>
      <li><a href="#">Reporting</a></li>
    </ul>
    <ul class="right" style="display:none;">
        <li><a href="javascript:void(0)"  title="Load" id="loadworkspace">Load</a></li>
        <li><a href="javascript:void(0)"  title="Save" id="saveworkspace">Save</a></li>
        <li><a href="javascript:void(0)" class="my_modal_open" title="Customize Dashboard">Customize Dashboard</a></li>
	</ul>
  </nav>
<!--
	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
	)); ?>--><!-- breadcrumbs -->

	<?php echo $content; ?>


</div><!-- page -->
<div class="search_open" style="display:none" id="popup1">
  	<div class="popupwin">
        <div class="headerpopup">
            <h4>Current Menu Item</h4>
            <a href="javascript:void(0)" class="close12">×</a>
        </div>
        <div id="accordion" class="ui-state-active"></div>
    </div>
  </div>
<input type="hidden" id="curDashboard" value="2"  />
<div class="search_open" style="display:none" id="popup2">
  	<div class="popupwin">
        <div class="headerpopup">
            <h4>Customize Dashboard</h4>
            <a href="javascript:void(0)" class="close12">×</a>
        </div>
        <div class="customizeblocks">
            <ul>
                <li><div class="block" id ="1"><p>100%</p></div></li>
                <li><div class="block" id ="2"><p>50%</p><p>50%</p></div></li>
                <li><div class="block" id ="3"><p>25%</p><p>25%</p><p>25%</p><p>25%</p></div></li>
                <li><div class="block" id ="4"><p style="width:75%">75%</p><p style="width:25%">25%</p></div></li>
                <li><div class="block" id ="5"><p style="width:40%">40%</p><p style="width:60%">60%</p></div></li>
                <li><div class="block" id ="6"><p style="width:90%">90%</p><p style="width:10%">10%</p></div></li>
            </ul>
        </div>
    </div>
</div>

<div class="search_open" style="display:none" id="popup3">
  	<div class="popupwin">
        <div class="headerpopup">
            <h4>Save Dashboard</h4>
            <a href="javascript:void(0)" class="close12">×</a>
        </div>
        <div style="padding:10px">
            <form id="loadForm" class="searchblock">
            <input type="text" id="workspaceName" class="searchfield" />
            <input type="button" onclick="saveWorkspace('DEFAULT')" class="exclusive" value="Save" />
            </form>
        </div>
    </div>
</div>

<div class="search_open" style="display:none" id="popup4">
  	<div class="popupwin">
        <div class="headerpopup">
            <h4>Load Dashboard</h4>
            <a href="javascript:void(0)" class="close12">×</a>
        </div>
        <div id="loadDash" style="padding:10px">
          
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/newcss/global.css" />
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
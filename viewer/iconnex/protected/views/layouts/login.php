<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="description" content=".">
<meta name="keywords" content="">
<meta name="language" content="en-uk, english">
<meta name="DC.description" content="">
<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
<meta name="viewport" content="width=device-width" />
<meta name="HandheldFriendly" content="true" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<title>iConnex Web Interface</title>
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/newcss/login.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/newcss/responsive.css" />

<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/newjs/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/newjs/jquery.popupoverlay.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/newjs/global.js"></script>

<!--[if lt IE 9]>
<script src="js/html5.js"></script>
<![endif]-->

<!--[if gte IE 9]>
<style type="text/css">
.gradient {filter: none;}
</style>
<![endif]-->
</head>

<body>
<div class="main">
	<div class="loginblock"></div>
	<div class="loginfield">
			<h1>iConnex<i>Web</i></h1>
			<h3>Please enter Your User ID and Password to Sign In!</h3>
			<div class="loginbox">
				<div class="block">
					<?php echo $content; ?> 
				</div>
			</div>
		</div>					
</div>
</body>
</html>

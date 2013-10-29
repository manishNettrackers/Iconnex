
<script> var iconnexUser ='<?php echo yii::app()->user->id; ?>'; </script>
<script> var menuCode ='nomenu'; </script>

<html lang="en">
    <head>
        <meta charset="utf-8" />
  <!--      <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
       <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/session.js"></script>
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/dashboard.js"></script>
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/golap.js"></script>
         -->
         <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/timePicker.js"></script> 
         <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/timeTableViewerCriteria.js"></script> 

        <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/timePicker.css" /> 
           
 
    </head>
    
    <body>
        <div id="content">
            <?php echo $content; ?>
        </div>
    </body>
</html>

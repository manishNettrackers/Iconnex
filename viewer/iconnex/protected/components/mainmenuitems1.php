<?php
            $this->widget($menuwidget,array(
            'items'=>array(
                array('label'=>'Home', 'url'=>array('/site/index'), 'view'=>'mainmenu'),
                array('label'=>'PWI', 'url'=>array('/pwi/pwi'), 'active'=>Yii::app()->controller->id=='pwi'),
                array('label'=>'iConnex', 'url'=>array('/infohost/index','view'=>'mainmenu'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Administrator')),
                //array('label'=>'Driver Performance', 'url'=>array('/golap/golap/driving'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Driver') , 'active'=>Yii::app()->controller->id=='driver'),
                //array('label'=>'Locations', 'url'=>array('/golap/golap/locations'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Authority') || Yii::app()->user->allowedAccess('role', 'Administrator'), 'active'=>Yii::app()->controller->id=='locations'),
                array('label'=>'Operations', 'url'=>array('/golap/golap/operations'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Administrator') || Yii::app()->user->allowedAccess('role', 'Bus Operator') || Yii::app()->user->allowedAccess('role', 'Authority'), 'active'=>Yii::app()->controller->id=='operations'),
                //array('label'=>'Network', 'url'=>array('/golap/golap/networkManagement'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Administrator') || Yii::app()->user->allowedAccess('role', 'Authority'), 'active'=>Yii::app()->controller->id=='network'),
                //array('label'=>'Telematics', 'url'=>array('/golap/golap/telematics'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Administrator') || Yii::app()->user->allowedAccess('role', 'Operator'), 'active'=>Yii::app()->controller->id=='telem'),
                //array('label'=>'Performance', 'url'=>array('/golap/golap/systemPerformance'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Administrator') || Yii::app()->user->allowedAccess('role', 'Authority'), 'active'=>Yii::app()->controller->id=='performance'),
                //array('label'=>'Maintenance', 'url'=>array('/golap/golap/systemMaintenance'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Administrator'), 'active'=>Yii::app()->controller->id=='maint'),
                //array('label'=>'Performance Dashboard', 'url'=>array('/golap/golap/perfdash'), 'visible'=>Yii::app()->user->allowedAccess('role', 'Bus Operator'), 'active'=>Yii::app()->controller->id=='perfdash'),
                //array('label'=>'Support Centre', 'url'=>array('/fms/index'), 'active'=>Yii::app()->controller->id=='fms'),
                array('label'=>'Login', 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
                array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest)
            ),
        )); 

?>

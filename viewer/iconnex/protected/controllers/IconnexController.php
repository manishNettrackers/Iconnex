<?php
 
class IconnexController extends Controller {

    /**
     * Select layout based on User and Action
     */
    public function getActionLayout()
    {   
         Yii::app()->user->allowedAccess('role', 'Administrator');
        $layout = "main";
        if ( Yii::app()->user->allowedAccess('role', 'Authority') )
            $layout = "rbcmain";
        else if ( Yii::app()->user->allowedAccess('role', 'Bus Operator') )
            $layout = "readingbuses";
        return $layout;
    }  

}


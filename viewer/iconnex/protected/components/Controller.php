<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

    function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = $this->getActionLayout();
    }

    /**
     * Select layout based on User and Action
     */
    public function getActionLayout()
    {   
        $layout = "main";
        if ( Yii::app()->user->name == "kferry" )
            $layout = "kferry";
        else if ( Yii::app()->user->allowedAccess('role', 'First Group Operator') )
            $layout = "firstgroup";
        else if ( Yii::app()->user->allowedAccess('role', 'Newbury District Operator') )
            $layout = "nandd";
        else if ( Yii::app()->user->allowedAccess('role', 'Wokingham Council') )
            $layout = "wokingham";
        else if ( Yii::app()->user->allowedAccess('role', 'Bracknell Council') )
            $layout = "bracknell";
        else if ( Yii::app()->user->allowedAccess('role', 'West Berks Council') )
            $layout = "wberks";
        else if ( Yii::app()->user->allowedAccess('role', 'Milton Keynes Council') )
            $layout = "mk";
        else if ( Yii::app()->user->allowedAccess('role', 'Bus Operator') )
            $layout = "readingbuses";
        else if ( Yii::app()->user->allowedAccess('role', 'Authority') )
            $layout = "rbcmain";
        return $layout;
    }  

}


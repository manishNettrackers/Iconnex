<?php
/**
* class PreferencesController extends Controller
* @Author Supriyo Jana
* @date 2013-07-06
* @function actions() Action that call class
* @function Preferences() Return array of userdata 
* @function performAjaxValidation() Performs the AJAX validation.
*/

class PreferencesController extends Controller
{
   
    function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = "//layouts/".$this->getActionLayout().'_golap_account';
    }

	
	/**
	 * function Preferences()
	 * @Author Supriyo Jana
	 * @date 2013-07-06
	 * @return array of user data
	 */

	public function actionPreferences()
	{
		$ls_user= Yii::app()->user->getId();//Yii::app()->user->getState('userid');
		$lo_usermodel = CentUser::model()->findByAttributes(array('usernm'=>$ls_user));
		
		if(isset($_POST['CentUser']))
		{	$this->performAjaxValidation($lo_usermodel);
			$lo_usermodel->attributes = $_POST['CentUser'];
			
			if($lo_usermodel->validate())
			{
					$new_password = CentUser::model()->findByPk((int)$ls_user);
					$po_previousState = clone $new_password;
					$new_password->usernm=$_POST['CentUser']["usernm"];
					$new_password->emailad=$_POST['CentUser']["emailad"];
					
					if($_POST['CentUser']["passwd_md5"]!='')
						{
							 $new_password->passwd_md5 = md5($_POST['CentUser']["passwd_md5"]);
							
						}else{
							$new_password->passwd_md5 =$new_password->passwd_md5;
						}
			
					if($new_password->save())
					{
						$po_finalState = $new_password;
						$la_result = array_diff($po_previousState->attributes, $po_finalState->attributes);
						$ls_message=array();
						if (!empty($la_result))
							{
								foreach($la_result as $key=>$val)
								{
										if($key=='usernm')
										{
											$ls_message[]="Your username has been updated.";
										}
										else if($key=='emailad')
										{
											$ls_message[]="Your email has been updated.";
										}
										else if($key=='passwd_md5')
										{
											$ls_message[]="Your password has been updated.";
										}
								}
							}
							else
							{
								$ls_message[]="No record updated.";
							}
						Yii::app()->user->setFlash('User',$ls_message);
					}
					
			}
			
		}
		
		$this->render('update',array(
			'model'=>$lo_usermodel,
		));
    }

   	protected function performAjaxValidation($lo_usermodel)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
		{
			
			echo CActiveForm::validate($lo_usermodel);
			
			Yii::app()->end();
		}
		
	} 
}

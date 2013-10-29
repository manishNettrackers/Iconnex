<?php
/**
* class IconnexUserController extends Controller
* @Author Supriyo Jana
* @date 2013-06-18
* @function actionCreate() Creates a new model.
* @function actionUpdate() Update a new model.
* @function actionDelete() Deletes a new model.
* @function loadModel() Returns the data model based on the primary key given in the GET variable.
*/
class IconnexUserController extends Controller
{
	function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = "//layouts/".$this->getActionLayout();
    }
public function actionUser()
	{
		
		$model=new IconnexUser('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['IconnexUser']))
			$model->attributes=$_GET['IconnexUser'];

		$this->render('userlist',array(
			'model'=>$model,
		));
	}

	/**
	 * public function actionCreate()
	 * @Author Supriyo Jana
	 * @date 2013-06-18
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{

		$model=new IconnexUser;
		$model_new=new IconnexMenuUser;
		$la_mappingmenu=array();
		
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['IconnexUser']))
		{



			$model->attributes=$_POST['IconnexUser'];
			$model->passwd_md5=md5($_POST['IconnexUser']["passwd_md5"]);
            $valid=$model->validate();  
                    if($valid){
									if($model->save())
									{
										// Save data into user menu mapping table
										if(isset($_REQUEST['IconnexMenuUser']['menu_id']))
										{
											foreach($_REQUEST['IconnexMenuUser']['menu_id'] as $key=>$val)
											{
												if($val!='')
												{
													$model_menuMapping=new IconnexMenuUser;
													$model_menuMapping->user_id=$model->userid;
													$model_menuMapping->menu_id=$val;
													$model_menuMapping->autorun='0';
													$model_menuMapping->show_accordion='1';
													$model_menuMapping->show_accordion='0';
													$model_menuMapping->save();
												}
												
											}
										}
										//===========================================
										
									   //do anything here
										 echo CJSON::encode(array(
											  'status'=>'success'
										 ));
										Yii::app()->end();
									}
                        }
                        else{
                            $error = CActiveForm::validate($model);
                            if($error!='[]')
                                echo $error;
                            Yii::app()->end();
                        }
			
			
			
			
		}
		//else{
//			die('+++++++');	
//			}
		$this->renderPartial('_form',array('model'=>$model,'model_new'=>$model_new,'mappingmenu'=>$la_mappingmenu));
	
	}

	/**
	 * public function actionUpdate($id)
	 * @Author Supriyo Jana
	 * @date 2013-06-18
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		
		$model_new=new IconnexMenuUser;
		$la_mappingmenu=array();
		$lo_mappingmenu=IconnexMenuUser::model()->findAllByAttributes(array('user_id'=>$id));
		$model=$this->loadModel($id);
		
		
		foreach($lo_mappingmenu as $key=>$value)
		{
			$la_mappingmenu[$value->menu_id]=array('selected' => 'true');
		}
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['IconnexUser']))
		{
			$model->attributes=$_POST['IconnexUser'];
			if($_POST['IconnexUser']["passwd_md5"]!='')
			{
				$model->passwd_md5 = md5($_POST['IconnexUser']["passwd_md5"]);
			}
			else
			{
				$model_user=IconnexUser::model()->findByPk((int)$id);
				$model->passwd_md5 =$model_user->passwd_md5;
			}
			
			//$this->performAjaxValidation($model);
			
			 $valid=$model->validate();            
                    if($valid){
								if($model->save())
									{
										IconnexMenuUser::model()->deleteAll(array("condition"=>"user_id=:user_id", "params"=>array(":user_id"=>$id)));
										// Save data into user menu mapping table
										if(isset($_REQUEST['IconnexMenuUser']['menu_id']))
										{
											foreach($_REQUEST['IconnexMenuUser']['menu_id'] as $key=>$val)
											{
												if($val!='')
												{
													$model_menuMapping=new IconnexMenuUser;
													$model_menuMapping->user_id=$model->userid;
													$model_menuMapping->menu_id=$val;
													$model_menuMapping->autorun='0';
													$model_menuMapping->show_accordion='1';
													$model_menuMapping->show_accordion='0';
													$model_menuMapping->save();
												}
											}
										}
										//do anything here
										 echo CJSON::encode(array(
											  'status'=>'success'
										 ));
										Yii::app()->end();
										//===========================================
									}
							  }
								else{
                            $error = CActiveForm::validate($model);
                            if($error!='[]')
                                echo $error;
                            Yii::app()->end();
                        }
		}
		$this->renderPartial('_form',array('model'=>$model,'model_new'=>$model_new,'mappingmenu'=>$la_mappingmenu));
	}

	/**
	 * public function actionDelete($id)
	 * @Author Supriyo Jana
	 * @date 2013-06-18
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		IconnexMenuUser::model()->deleteAll(array("condition"=>"user_id=:user_id", "params"=>array(":user_id"=>$id)));	
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();	

	}



	/**
	 * public function loadModel($id)
	 * @Author Supriyo Jana
	 * @date 2013-06-18
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=IconnexUser::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('b.IconnexUser','The requested page does not exist.'));
		return $model;
	}

	
}

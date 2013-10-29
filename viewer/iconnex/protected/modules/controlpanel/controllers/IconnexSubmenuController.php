<?php
/**
* class IconnexSubmenuController extends Controller
* @date 2013-06-26
* @function actions() Action that call class
* @function filters() Return array of filter method
* @function filterAdminActions() Add extra feature to admin action
* @function actionView() Displays a particular model.
* @function actionCreate() Creates a new model.
* @function actionUpdate() Update a new model.
* @function actionDelete() Deletes a new model.
* @function actionIndex() List all model.
* @function actionAdmin() Manage all model.
* @function loadModel() Returns the data model based on the primary key given in the GET variable.
* @function performAjaxValidation() Performs the AJAX validation.
*/
class IconnexSubmenuController extends Controller
{
	function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = "//layouts/".$this->getActionLayout();
    }
	public $breadcrumbs=array();

	/**
	 * function filters()
	 	 * @date 2013-06-26
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * public function actionCreate()
	 	 * @date 2013-06-26
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new IconnexSubmenu;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['IconnexSubmenu']))
		{
			
			$model->attributes=$_POST['IconnexSubmenu'];
			
                    $valid=$model->validate();  
                    if($valid){
						$model->save();
						$li_max =Yii::app()->db->createCommand("SELECT max(menu_no) as max FROM `iconnex_menuitem` where menu_id='".$_REQUEST['menu_id']."' ")->queryRow();

						$lo_mappingmodel=new IconnexMenuitem;
						$lo_mappingmodel->menu_id=$_REQUEST['menu_id'];
						$lo_mappingmodel->menu_no=$li_max['max']+1;
						$lo_mappingmodel->app_id=$model->app_id;
						$lo_mappingmodel->run_location='FULLSCREEN';
						$lo_mappingmodel->save();
						
						
                       //do anything here
                         echo CJSON::encode(array(
                              'status'=>'success'
                         ));
                        Yii::app()->end();
                        }
                        else{
                            $error = CActiveForm::validate($model);
                            if($error!='[]')
                                echo $error;
                            Yii::app()->end();
                        }
			
			
			
			
		}

		$this->renderPartial('_form',array(
			'model'=>$model,
		));
	}

	/**
	 * public function actionUpdate($id)
	 * @date 2013-06-26
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['IconnexSubmenu']))
		{
			$this->performAjaxValidation($model);
			$model->attributes=$_POST['IconnexSubmenu'];
			 $valid=$model->validate();            
                    if($valid){
									$model->save();
									$model->attributes='';
									 echo CJSON::encode(array('status'=>'success'));
                       				 Yii::app()->end();
									
								}
								else{
                            $error = CActiveForm::validate($model);
                            if($error!='[]')
                                echo $error;
                            Yii::app()->end();
                        }
		}

		$this->renderPartial('_form',array(
			'model'=>$model,
		));
	}

	/**
	 * public function actionDelete($id)
	 	 * @date 2013-06-26
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			//Delete from mapping table
				IconnexMenuitem::model()->deleteAll(array("condition"=>"app_id=:app_id", "params"=>array(":app_id"=>$id)));
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();			
			
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
			{
				//$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
			}
			
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	
	/**
	 * public function actionAdmin()
	 	 * @date 2013-06-26
	 * Manages all models.
	 */
	public function actionMenu()
	{
		$model=new IconnexSubmenu('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['IconnexSubmenu']))
			$model->attributes=$_GET['IconnexSubmenu'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * public function loadModel($id)
	 	 * @date 2013-06-26
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=IconnexSubmenu::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * public function performAjaxValidation($model)
	 	 * @date 2013-06-26
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='iconnex-submenu-form')
		{ 
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

<?php
/**
* class IconnexMenuController extends Controller
* @Author Supriyo Jana
* @date 2013-06-18
* @function actionCreate() Creates a new model.
* @function actionUpdate() Update a new model.
* @function actionDelete() Deletes a new model.
* @function loadModel() Returns the data model based on the primary key given in the GET variable.
*/
class IconnexMenuController extends Controller
{
	function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = "//layouts/".$this->layout.'_golap_account';
    }
public function actionMenu()
	{
		/*$model=iconnexMenu::model()->findAll();
		$this->render('menulist',array(
			'model'=>$model,
		));	
		*/
		
		
		$model=new iconnexMenu('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['menu']))
			$model->attributes=$_GET['menu'];

		$this->render('menulist',array(
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
		$model=new iconnexMenu;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_REQUEST['menu']) && $_REQUEST['menu']!='')
		{
			$model->menu_name=$_REQUEST['menu'];
			if($model->save())
			{
				$model=iconnexMenu::model()->findAll();
				$this->renderPartial('update',array(
					'model'=>$model,
				));	
			}	
		}
	}

	/**
	 * public function actionUpdate($id)
	 * @Author Supriyo Jana
	 * @date 2013-06-18
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate()
	{
		
		$model=$this->loadModel($_REQUEST['pk']);

		if(isset($_REQUEST['pk']))
		{
			$model->menu_name=$_REQUEST['value'];
			if($model->save())
			{
				//$this->redirect(array('menu'));
			}	
		}
	}

	/**
	 * public function actionDelete($id)
	 * @Author Supriyo Jana
	 * @date 2013-06-18
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete()
	{

		$id=$_REQUEST['id'];
		if($id!='')
		{
			
			$loa_mappingMenu=IconnexMenuitem::model()->findAllByAttributes(array("menu_id"=>$id));//getting data form  iconnex_menuitem
			//Delete data from iconnex_application
				foreach($loa_mappingMenu as $ls_key=>$ls_val)
				{
					$lo_submenu=IconnexSubmenu::model()->findByPk((int)$ls_val->app_id);
					$lo_submenu->delete();
				}
			//Delete from mapping table
				IconnexMenuitem::model()->deleteAll(array("condition"=>"menu_id=:menu_id", "params"=>array(":menu_id"=>$id)));
			
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();	
				
			
		}

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
		$model=iconnexMenu::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('b.iconnexMenu','The requested page does not exist.'));
		return $model;
	}

	
}

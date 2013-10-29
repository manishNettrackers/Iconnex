<?php

class VehicleController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
        if(Yii::app()->user->allowedAccess('task', 'Vehicle Manager') || Yii::app()->user->allowedAccess('task', 'Vehicle Viewer'))
        {
            // delete the post
		    return array(
			    array('allow',  // allow all users to perform 'index' and 'view' actions
				    'actions'=>array('index','view','vehicle','updatep'),
				    'users'=>array(Yii::app()->user->getId()),
                )
			);
        }
        else
        {
            // delete the post
		    return array(
			    array('deny',  // allow all users to perform 'index' and 'view' actions
				    'actions'=>array('index','view','vehicle','updatep'),
				    'users'=>array('*'),
                )
			);
        }
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Vehicle;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['Vehicle']))
		{
			$model->attributes=$_POST['Vehicle'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->vehicle_id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdatep()
	{
        $id = $_GET["ids"][0];
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		//$this->performAjaxValidation($model);

		if(isset($_GET['Vehicle']))
		{
			$model->attributes=$_GET['Vehicle'];
			if($model->save())
				echo "Ok";
		    else
			    throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
        else
        {
		    $this->renderPartial('update',array(
			    'model'=>$model,
		    ));
        }
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate()
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['Vehicle']))
		{
			$model->attributes=$_POST['Vehicle'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->vehicle_id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Vehicle');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

    public function actionVehicle()
    {
        $model = new Vehicle;
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Vehicle']))
			$model->attributes=$_GET['Vehicle'];

        //$form  = new CForm('application.views.forms.vehicleForm', $model);
        //if($form->submitted() && $form->validate())
        //{
            //$this->redirect(Yii::app()->user->returnUrl);
        //}else{
                // display the login form
                $this->render('vehicle',array('model'=>$model));
        //}     
    }

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Vehicle('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Vehicle']))
			$model->attributes=$_GET['Vehicle'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Vehicle::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='vehicle-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

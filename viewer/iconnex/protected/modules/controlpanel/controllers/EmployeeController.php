<?php

class EmployeeController extends Controller
{
	function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = "//layouts/".$this->getActionLayout();
    }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Employee;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Employee']))
		{
			$model->attributes=$_POST['Employee'];
			//$model->employee_code=$model->getUniqueId();
			$model->operator_id=$_REQUEST['operator_id'];
			 $valid=$model->validate();
			 if($valid){
							if($model->save())
							{
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

		$this->renderPartial('_form',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Employee']))
		{
			$model->attributes=$_POST['Employee'];
			$model->operator_id=$_REQUEST['operator_id'];
			 $valid=$model->validate();
			if($valid){
							if($model->save())
							{
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

		$this->renderPartial('_form',array(
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
		$dataProvider=new CActiveDataProvider('Employee');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionBusdriver()
	{
		$model=new Employee('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Employee']))
			$model->attributes=$_GET['Employee'];

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
		$model=Employee::model()->findByPk((int)$id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='employee-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

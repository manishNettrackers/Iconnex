<?php

class UnitparamController extends Controller
{
	public function actionBuildcomponent()
	{
		$this->render('buildcomponent');
	}

	public function actionBuildparameter()
	{
		$this->render('buildparameter');
	}

	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionParamsForBuildAndComponent()
	{

        $comp_id = $_GET["component_id"];
        $build_id = $_GET["build_id"];
		if ( is_array ( $comp_id ) ) $comp_id = $comp_id[0];
		if ( is_array ( $build_id ) ) $build_id = $build_id[0];
		$modelbuild=$this->loadModelBuild($comp_id, $build_id);

		// Uncomment the following line if AJAX validation is needed
		//$this->performAjaxValidation($model);

echo "cons<BR>";
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
			$model=new Unitparam;
			$model->unsetAttributes();  // clear any default values
		    $this->render('component_params',array(
			    'modelComponent'=>$modelComponent,
			    'modelBuild'=>$modelbuild,
		    ));
        }
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}

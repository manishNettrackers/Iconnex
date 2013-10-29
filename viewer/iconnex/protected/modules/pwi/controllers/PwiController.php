<?php

class PwiController extends Controller
{
    function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = "//layouts/".$this->getActionLayout();
    }

	public function actionBuses()
	{
		$this->renderPartial('buses');
	}

	public function actionDeps()
	{
		$this->renderPartial('deps');
	}

	public function actionIndex()
	{
		$this->render('index');
	}

	public function actionMessages()
	{
		$this->renderPartial('messages');
	}

	public function actionRoutes()
	{
		$this->renderPartial('routes');
	}

	public function actionSearch()
	{
		$this->renderPartial('search');
	}

	public function actionStops()
	{
		$this->renderPartial('stops');
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

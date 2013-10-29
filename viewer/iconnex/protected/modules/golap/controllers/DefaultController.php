<?php

class DefaultController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}

	public function actionLogin()
	{
    echo "LOIGN"; die;
		$this->render('index');
	}
}

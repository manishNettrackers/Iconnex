<?php

class SubscriptionController extends Controller
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
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('allow',
				'actions'=>array('locs'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('modify'),
				'users'=>array('admin'),
			),
			array('allow',
				'actions'=>array('parameters'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
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
		$model=new Subscription;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Subscription']))
		{
			$model->attributes=$_POST['Subscription'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->subscription_id));
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
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Subscription']))
		{
			$model->attributes=$_POST['Subscription'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->subscription_id));
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
		$dataProvider=new CActiveDataProvider('Subscription');
        $model = new Subscription;
        $model->unsetAttributes();  // clear any default values
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'model'=>$model,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
        $data['locations'] = array();

		$model = new Subscription('search');
		$model->unsetAttributes();  // clear any default values

		if(isset($_GET['subscriber_id']))
        {
			$model->subscriber_id = $_GET['subscriber_id'];
            $subscriber = Subscriber::model()->find("t.subscriber_id = " . $_GET["subscriber_id"]);
            $data["userLocsAvailable"] = $this->getUserLocs($subscriber->user_id);
            $data["userLocsSubscribed"] = $this->getUserLocsSubscribed($subscriber->user_id);
        }

		$this->render('admin',array(
            'message' => $this->_getMessage(),
			'model'=>$model,
			'data'=>$data,
		));
	}

	public function actionParameters()
	{
        $data['locations'] = array();

		$model = new Subscription;
		$model->unsetAttributes();  // clear any default values
/*
		if(isset($_GET['subscriber_id']))
        {
			$model->subscriber_id = $_GET['subscriber_id'];
            $subscriber = Subscriber::model()->find("t.subscriber_id = " . $_GET["subscriber_id"]);
            $data["userLocsAvailable"] = $this->getUserLocs($subscriber->user_id);
            $data["userLocsSubscribed"] = $this->getUserLocsSubscribed($subscriber->user_id);
        }
*/
		$this->render('parameters',array(
            'message' => $this->_getMessage(),
			'model'=>$model,
//			'data'=>$data,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Subscription::model()->findByPk((int)$id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='subscription-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

    public function actionLocs() {
        $this->_setMessage("");
        $subscriber = Subscriber::model()->find("t.subscriber_id = " . $_POST["Subscription"]["subscriber_id"]);
        $data["userLocsAvailable"] = $this->getUserLocs($subscriber->user_id);
        $data["userLocsSubscribed"] = $this->getUserLocsSubscribed($subscriber->user_id);
        $this->renderPartial('locations',
            array('model' => $subscriber, 'message' => $this->_getMessage(), 'subscriber_id' => $subscriber->subscriber_id, 'data' => $data),
            false,
            true);
    }

    private function getUserLocs($userid) {
        $criteria = new CDbCriteria();
        $criteria->join = ", location";
        $criteria->condition = "t.user_id = $userid
            AND location.location_id = t.location_id
            AND t.location_id NOT IN
            (SELECT subscr_loc.location_id
            FROM cent_user, subscriber, subscription, subscr_loc
            WHERE subscriber.user_id = cent_user.userid
            AND subscription.subscriber_id = subscriber.subscriber_id
            AND subscr_loc.subscription_id = subscription.subscription_id)";
        $criteria->order = "location.location_code";

        $available = UserLoc::model()->findAll($criteria);
        if ($available === null)
            return array();
        return $available;
    }

    private function getUserLocsSubscribed($userid) {
        $criteria = new CDbCriteria();
        $criteria->join = ", cent_user, subscriber, subscription, subscr_loc, location";
        $criteria->condition = "t.user_id = $userid
            AND cent_user.userid = t.user_id
            AND subscriber.user_id = cent_user.userid
            AND subscription.subscriber_id = subscriber.subscriber_id
            AND subscr_loc.subscription_id = subscription.subscription_id
            AND subscr_loc.location_id = t.location_id
            AND location.location_id = subscr_loc.location_id";
        $criteria->order = "location.location_code";

        $subscribed = UserLoc::model()->findAll($criteria);
        if ($subscribed === null)
            return array();
        return $subscribed;
    }

    public function actionModify() {
        $subscriber = isset($_POST["Subscription"]["subscriber_id"]) ? Subscriber::model()->find("t.subscriber_id = " . $_POST["Subscription"]["subscriber_id"]) : "";
        $locs = isset($_POST['UserLoc']["location_id"]) ? $_POST['UserLoc']["location_id"] : ""; 
        $addLocs = Yii::app()->request->getParam('addLocs', 0);
        $removeLocs = Yii::app()->request->getParam('removeLocs', 0);
        $params = Yii::app()->request->getParam('params', 0);
        if (is_array($locs))
        {
            if ($addLocs)
            {
                $this->_addLocs($subscriber->subscriber_id, $locs);
                $this->_setMessage("Location(s) Added");
            }
            else
            {
                if ($removeLocs)
                {
                    $this->_removeLocs($subscriber->subscriber_id, $locs);
                    $this->_setMessage("Location(s) Removed");
                }
            }
            if ($params)
            {
                $criteria = new CDbCriteria();
                $criteria->join = ", subscr_loc";
                $criteria->condition = "t.subscriber_id = $subscriber->subscriber_id
                    AND subscr_loc.subscription_id = t.subscription_id
                    AND subscr_loc.location_id = $locs[0]";
                $model = Subscription::model()->find($criteria);
                $this->renderPartial('parameters', array('model' => $model, $subscriber->subscriber_id, $locs));
            }
        }

        $data['locations'] = array();
		$model = new Subscription('search');
		$model->unsetAttributes();
        $model->subscriber_id = $subscriber->subscriber_id;
        $data["userLocsAvailable"] = $this->getUserLocs($subscriber->user_id);
        $data["userLocsSubscribed"] = $this->getUserLocsSubscribed($subscriber->user_id);
        $this->renderPartial('locations', array('model' => $model, 'message' => $this->_getMessage(), 'data' => $data));
    }

    private function _addLocs($subscriber_id, $locs) {
        if ($subscriber_id) {
            foreach ($locs as $loc) {
                $s = new Subscription;
//                $s->subscription_id = 0;
                $s->subscriber_id = $subscriber_id;
                $s->subscription_type = "CONSUME";
                $s->creation_time = date("Y-m-d G:i:s");
//                $s->start_time = "CURRENT";
//                $s->end_time "CURRENT + 1 UNITS YEAR"
//                $s->subscribed_time = 
                $s->update_interval = 20;
                $s->max_departures = 9;
                $s->display_thresh = 3600;
//                $s->request_id
//                $s->disabled           A
//                $s->subscription_ref   
                $s->save();

                $sl = new SubscrLoc;
                $sl->subscription_id = $s->getPrimaryKey();
                $sl->location_id = $loc;
                $sl->save();
            }
        }
    }

    private function _removeLocs($subscriber_id, $locs) {
        if ($subscriber_id > 0) {
            $sid = 0;
            $criteria = new CDbCriteria();
            $criteria->join = ", subscription";

$f = fopen("/tmp/zzz", "w");
            foreach ($locs as $l) {
                unset($sl);
                $criteria->condition = "t.location_id = $l
                    AND subscription.subscriber_id = $subscriber_id
                    AND t.subscription_id = subscription.subscription_id";
                $slocs = SubscrLoc::model()->findAll($criteria);
                foreach ($slocs as $sl)
                {
                    // This assumes there is only one location per subscription
                    // which should be the case for SIRI but wasn't for RTIG.
                    $sid = $sl->subscription_id;
                    $deleted = SubscrLoc::model()->deleteAll("subscription_id = $sid");
                    fwrite($f, "_removeLocs deleted $deleted subscr_loc rows with subscription_id $sid\n");
                    $delete = Subscription::model()->deleteAll("subscription_id = $sid");
                    fwrite($f, "_removeLocs deleted $deleted subscription rows with subscription_id $sid\n");
                }
            }
fclose($f);
        }
    }

    private function _setMessage($mess) {
        Yii::app()->user->setState("message", $mess);
    }

    private function _getMessage() {
        return Yii::app()->user->getState("message");
    }

}


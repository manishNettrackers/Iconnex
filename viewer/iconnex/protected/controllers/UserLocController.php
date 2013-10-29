<?php

class UserLocController extends Controller
{
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
                'actions'=>array('locSearch','locs', 'modify'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

	public function actionAdmin()
	{
        $this->_setMessage("");

        $subscriber = new Subscriber;
        if(isset($_GET['subscriber_id']))
        {   
            $subscriber = Subscriber::model()->find("subscriber_id = " . $_GET["subscriber_id"]);
            $user_id = $subscriber->user_id;
        }   
        else
            $user_id = Subscriber::model()->find(array('order'=>'subscriber_code'))->user_id;

        $this->prepareLocsPermitted($user_id);
        $data["locsPermitted"] = $this->getLocsPermitted();
        $data["locsAvailable"] = $this->getLocsAvailable($user_id);
//        $data['locations'] = array();

        $this->render('admin',array(
            'message' => $this->_getMessage(),
            'model'=>$subscriber,
            'data'=>$data,
        ));
    }

    public function actionLocSearch() {
        $this->_setMessage("");
        $d = print_r($_GET, true);
        $d = print_r($_POST, true);

        $subscriber = isset($_POST["Subscriber"]["subscriber_id"]) ? Subscriber::model()->find("t.subscriber_id = " . $_POST["Subscriber"]["subscriber_id"]) : Subscriber::model()->find();
        $criteria = new CDbCriteria();
        $criteria->join = ", t_permitted_locs";
        $criteria->condition .= "t.location_id NOT IN
            (SELECT user_loc.location_id
            FROM user_loc
            WHERE user_loc.user_id = " . $subscriber->user_id . ")";

        if (strlen($_POST["q"]) > 0)
        {
// SEE ZZZ HACK IN framework/db/schema/informix/CInformixCommandBuilder.php
//            $criteria->distinct = true;
            $criteria->join .= ", service, service_patt";
            $criteria->condition .= " AND service.description = \"" . $_POST["q"] . "\"
                AND service_patt.service_id = service.service_id
                AND service.wef_date < CURRENT
                AND service_patt.location_id = t.location_id";
    /*            --and service.wet_date > CURRENT */
        }
        $criteria->condition .= " AND t.location_id != t_permitted_locs.location_id";
        $criteria->order = "t.description";

        $this->prepareLocsPermitted($subscriber->user_id);
        $data["locsPermitted"] = $this->getLocsPermitted();
        $data["locsAvailable"] = Location::model()->findAll($criteria);
        $this->renderPartial('locations',
            array('model' =>$subscriber, 'message' => $this->_getMessage(), 'subscriber_id' => $subscriber->subscriber_id, 'data' => $data, 'search' => $_POST["q"]),
            false,
            true);
    }

    public function actionLocs() {
        $this->_setMessage("");
        $subscriber = Subscriber::model()->find("t.subscriber_id = " . $_GET["Subscriber"]["subscriber_id"]);
        $this->prepareLocsPermitted($subscriber->user_id);
        $data["locsPermitted"] = $this->getLocsPermitted();
        $data["locsAvailable"] = $this->getLocsAvailable($subscriber->user_id);
        $this->renderPartial('locations',
            array('model' => $subscriber, 'message' => $this->_getMessage(), 'subscriber_id' => $subscriber->subscriber_id, 'data' => $data, 'search' => ""),
            false,
            true);
    }

    private function prepareLocsPermitted($userid) {
        $rowsAffected = Yii::app()->db->createCommand("SELECT t.location_id FROM location t, user_loc where user_loc.location_id = t.location_id AND user_loc.user_id = $userid INTO TEMP t_permitted_locs WITH NO LOG")->execute();
    }

    private function getLocsPermitted() {
        $criteria = new CDbCriteria();
        $criteria->join = ", t_permitted_locs";
        $criteria->condition = "t.location_id = t_permitted_locs.location_id";
        $criteria->order = "location_code";
        $permitted = Location::model()->findAll($criteria);
        if ($permitted === null)
            return array();
        return $permitted;
    }

    private function getLocsAvailable($userid) {
        $criteria = new CDbCriteria();
        $criteria->condition = "t.location_id NOT IN
            (SELECT location_id
            FROM t_permitted_locs)";
        $criteria->order = "location_code";
        $available = Location::model()->findAll($criteria);
        if ($available === null)
            return array();
        return $available;
    }

    public function actionModify() {
        $this->_setMessage("");

        if (isset($_POST["Subscriber"]["subscriber_id"]))
            $subscriber = Subscriber::model()->find("t.subscriber_id = " . $_POST["Subscriber"]["subscriber_id"]);
        else
        {
            $this->_setMessage("Error: subscriber_id not set");
            $this->actionAdmin();
        }
        $search = "";
        if (isset($_POST['q']))
            $search = $_POST['q'];

        $addLocs = Yii::app()->request->getParam('addLocs', 0);
        $removeLocs = Yii::app()->request->getParam('removeLocs', 0);
        $locs = isset($_POST['Location']["location_id"]) ? $_POST['Location']["location_id"] : "";
        if (is_array($locs))
        {   
            if ($addLocs)
            {
                $this->_addLocs($subscriber->user_id, $locs);
                $this->_setMessage("Location(s) Added");
            }
            else
            {
                if ($removeLocs)
                {
                    $this->_removeLocs($subscriber->user_id, $locs);
                    $this->_setMessage("Location(s) Removed");
                }
            }
        }

        $model = new Subscriber('search');
        $model->unsetAttributes();
        $model->subscriber_id = $subscriber->subscriber_id;
        $this->prepareLocsPermitted($subscriber->user_id);
        $data["locsPermitted"] = $this->getLocsPermitted();
        $data["locsAvailable"] = $this->getLocsAvailable($subscriber->user_id);
        $this->renderPartial('locations', array('model' => $model, 'message' => $this->_getMessage(), 'data' => $data, 'search' => $search));
    }

    private function _addLocs($userid, $locs) {
        if ($userid) {
            foreach ($locs as $l) {
                $ul = new UserLoc;
                $ul->user_id = $userid;
                $ul->location_id = $l;
                $ul->save();
            }
        }
    }

    private function _removeLocs($userid, $locs) {
        if ($userid > 0) {
            $criteria = new CDbCriteria();
            foreach ($locs as $l) {
                $deleted = UserLoc::model()->deleteAll("user_id = $userid AND location_id = $l");
            }
        }
    }

    private function _setMessage($mess) {
        Yii::app()->user->setState("message", $mess);
    }

    private function _getMessage() {
        return Yii::app()->user->getState("message");
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

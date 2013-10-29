<?php

class GolapController extends Controller
{
    public $menuCode = "";

    function __construct($id,$module=null) {
        parent::__construct($id,$module);
        $this->layout = $this->getActionLayout();
        $this->layout = "//layouts/".$this->layout.'_golap';
		//echo "manish"; exit; //layouts/main_golap
    }
 

	public function actionDriving()
	{
		$this->menuCode = 'Driver';
		$this->layout = '//layouts/driving';
		$this->render('driving');
	}

	public function actionOperations()
	{
        if ( !Yii::app()->user->allowedAccess('role', 'Authority') && !Yii::app()->user->allowedAccess('role', 'Bus Operator') && !Yii::app()->user->allowedAccess('role', 'Administrator') )
        {
            Yii::app()->user->loginRequired();
        }

		$this->menuCode = 'RTI Operations';
		$this->render('golap');
	}

	public function actionLocations()
	{
		$this->menuCode = 'Locations';
		$this->render('golap');
	}

	public function actionNetworkManagement()
	{
		$this->menuCode = 'Network Management';
		$this->render('golap');
	}

	public function actionTelematics()
	{
		$this->menuCode = 'Telematics';
		$this->render('golap');
	}

	public function actionSystemPerformance()
	{
		$this->menuCode = 'System Performance';
		$this->render('golap');
	}

	public function actionSystemMaintenance()
	{
		$this->menuCode = 'System Maintenance';
		$this->render('golap');
	}

	public function actionIndex()
	{
		$this->render('golap');
	}

	public function actionSmall()
	{
		$this->render('small');
	}

	public function actionGolap()
	{
		
		$this->render('index');
	}

	public function actionPerfdash()
	{
        $this->layout = "//layouts/perfdash_golap";
		$this->render('perfdash');
	}

	public function actionCriteria()
	{
		
		$this->renderPartial('criteria');
	}

	public function actionTest()
	{
		
		$this->render('test');
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
	
	public function actionDashboardSave()
	{
	
		$connection 	= Yii::app()->db;
		$userSql 		= "SELECT userid FROM cent_user WHERE usernm = '".$_GET['user']."'";
		$row		= $connection->createCommand($userSql)->queryRow();
		
		$sql  			= 'INSERT INTO iconnex_workspace SET user_id = :user_id,
		workspace_name = :workspace_name,
		dashboard_layout = :dashboard_layout;
		';
		$command	= $connection->createCommand($sql);

		$command->bindValue(":user_id",$row['userid'] );
		$command->bindValue(":workspace_name",  $_GET['layout_']);
		$command->bindValue(":dashboard_layout",$_GET['MANUAL_workspace']);
		$command->execute();
		$workspace_id	= Yii::app()->db->getLastInsertId();
		
		foreach ( $_GET as $k=>$v )
		{
			if (strpos($k,'workspace_title_') !== false)
			{
				$arr[$k] = $v;
			}
		}
		
		
		$wsct = 1;
		
		foreach( $arr as $k=>$v)
		{
			$sql  			= 'INSERT INTO iconnex_workspace_item SET workspace_id = :workspace_id,
			workspace_item_no = :workspace_item_no,
			workspace_menu_item = :workspace_menu_item;
			';
			$command	= $connection->createCommand($sql);
	
			$command->bindValue(":workspace_id", $workspace_id);
			$command->bindValue(":workspace_item_no",  $wsct);
			$command->bindValue(":workspace_menu_item",$v);
			$command->execute();
			$wsct ++;
		}
	}
	
	public function actionLoadAll()
	{
		$ls_userId 	= $_GET['user'];
		
		$connection = Yii::app()->db;
		$userSql 	= "SELECT userid FROM cent_user WHERE usernm = '".$_GET['user']."'";
		$row		= $connection->createCommand($userSql)->queryRow();
		
		$sql 		= "SELECT workspace_id,dashboard_layout FROM iconnex_workspace WHERE user_id = ".$row['userid'];
		$rows		= $connection->createCommand($sql)->queryAll();
		
		foreach( $rows as $row)
		{
			$arr[$row['workspace_id']] = $row['dashboard_layout'];
		}
		echo json_encode($arr);
		exit;
	}
	
	public function actionDashboardLoad()
	{
		$ls_userId 	= $_GET['user'];
		
		$connection = Yii::app()->db;
		$userSql 	= "SELECT userid FROM cent_user WHERE usernm = '".$_GET['user']."'";
		$row		= $connection->createCommand($userSql)->queryRow();
		
		$sql 		= "SELECT workspace_id,workspace_name FROM iconnex_workspace WHERE user_id = ".$row['userid']." AND workspace_id =".$_GET['workspace_id'];
		$command	= $connection->createCommand($sql)->queryRow();
			
		$sql1 		= "SELECT workspace_menu_item FROM iconnex_workspace_item WHERE workspace_id = ".$command['workspace_id'];
		$rows		= $connection->createCommand($sql1)->queryAll();
		
		$i = 1;
		foreach( $rows as $row)
		{
			$arr['workspace_item_'.$i] = $row['workspace_menu_item'];
			$i++;
		}
		$la_layout['layout'] = $this->getLayoutStructure($command['workspace_name']);
		$arr = array_merge($arr,$la_layout);
		
		echo json_encode($arr);
		exit;
	}
	
	protected function getLayoutStructure($layoutId)
	{
		switch($layoutId)
		{
			case 1: 
					return array(
						'dashcol1'=>'100%'
					);
					break;
			case 2: 
					return array(
						'dashcol1'=>'50%',
						'dashcol2'=>'50%'
					);
					break;
			case 3: 
					return array(
						'dashcol1'=>'25%',
						'dashcol2'=>'25%',
						'dashcol3'=>'25%',
						'dashcol4'=>'25%',
					);
					break;
			case 4: 
					return array(
						'dashcol1'=>'75%',
						'dashcol2'=>'25%'
					);
					break;
			case 5: 
					return array(
						'dashcol1'=>'40%',
						'dashcol2'=>'60%'
					);
					break;
			case 6: 
					return array(
						'dashcol1'=>'90%',
						'dashcol2'=>'10%'
					);
					break;
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

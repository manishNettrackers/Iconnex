<?php 
	function topmenustrip($in, $currentview)
	{
        $id = Yii::app()->user->getId();
        $admin_visible = false;

        $adminmenu = array();
//        if(Yii::app()->user->allowedAccess('role', 'Administrator'))
            $adminmenu[] = array('label'=>'User Authentication Maintenance', 'url'=>array('/srbac'));
 //       if(Yii::app()->user->allowedAccess('task', 'Vehicle Manager, Vehicle Viewer'))
            $adminmenu[] = array('label'=>'Vehicle Maintenance', 'url'=>array('/vehicle/vehicle'));
  //      if(Yii::app()->user->allowedAccess('task', 'Employee Manager,Employee Viewer'))
            $adminmenu[] = array('label'=>'Employee Maintenance', 'url'=>array('/employee/admin'));
   //     if(Yii::app()->user->allowedAccess('task', 'User Manager'))
            $adminmenu[] = array('label'=>'User Authentication', 'url'=>array('/srbac'));

		$in->widget('application.extensions.mbmenu.MbMenu',
			array('items'=>array( 
				array('label'=>'Trip Analysis', 
                    'active' => $currentview=='trips', 
                    'url'=>array('/infohost','view'=>'trips','target_menu'=>'trips', 'project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')
				),
				array('label'=>'Timetables', 
                	'active' => $currentview=='timetables', 
                    'url'=>array('/infohost','view'=>'timetables','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')
				),
				array('label'=>'Messaging', 
                    'active' => $currentview=='messages', 
                    'url'=>array('/infohost','view'=>'messages','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')
				),
				array('label'=>'Stop Analysis', 
                    'active' => $currentview=='stops', 
                    'url'=>array('/infohost','view'=>'stops','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex')
				),
//				array('label'=>'Passenger Counting', 
//                    'active' => $currentview=='icounts',
//                    'url'=>array('/infohost','view'=>'icounts','project'=>'icounts','execute_mode'=>'MENU', 'template'=>'iconnex')
//				),
				array('label'=>'Administration',
					'visible' => Yii::app()->user->allowedAccess('role', 'Administrator'), 
                    'active' => $currentview=='admin', 
                    'url'=>array('/infohost','view'=>'admin','project'=>'infohost','execute_mode'=>'MENU', 'template'=>'iconnex'),
                    'items'=>$adminmenu
				)
			))
		); // widget
	}

    function set_up_menu_session()
    {
        global $session_name;
    
        $session_name = false;
    
        // Check for Posted Session Name
        if (isset($_REQUEST['session_name']))
                $session_name = $_REQUEST['session_name'];
    
        if ( !$session_name )
        {   
            session_start();
            session_regenerate_id(false);
            $session_name = session_id();
            //$_SESSION = array();
        }
        else
        {   
            session_id($session_name);
            @session_start();
        }
    }

    function session_menu_request_item($in_item, $in_default = false, $in_default_condition = true)
    {
	    $ret = false;
	    if ( array_key_exists($in_item, $_SESSION) )
		    $ret = $_SESSION[$in_item];
    
	    if ( array_key_exists($in_item, $_REQUEST) )
		    $ret = $_REQUEST[$in_item];
    
	    if ( !$ret )
		    $ret = false;
	    
	    if ( $in_default && $in_default_condition && !$ret )
		    $ret = $in_default;
    
	    $_SESSION[$in_item] = $ret;
    
	    return ( $ret );
    }

?>

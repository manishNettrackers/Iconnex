<?php

/*
 * 
 * 
 * 
 */

class BusesController extends Controller {

    public function actionIndex() {

        $requestparams = requestHandler::processRequest()->getRequestParams();

        $criteria = array($requestparams['criteria']);

        $criteriaform = new operatorFormModel();

        //requestHandler::Log($criteria);



        //NEW THEME
        //   yii::app()->theme = "bootstrap";    

        $this->render('/criteria/index', $criteriaform);
    }

    public function actionTimetableViewer() {

        //trigger the the request process property
        $request = requestHandler::processRequest();

        //get the request params from the request object
        $params = $request->getRequestParams();


        if (isset($params['update'])) {
                    new userTimetableBuilder($params['datefrom'] , $params['dateto'] ,$params['timefrom'], $params['timeto'],$params['operatorid'],$params['routeid']);
        } else {
            new userTimetableBuilder(); 
        }

        //process the output format
        $to_return_object = $this->processOutputFormat('timetablemonitor', $params['outputformat']);

        //check if we have a valid object
        //  if (is_object($to_return_object) && isset($to_return_object)) {
        //return the response with the data    
        requestHandler::sendResponse($to_return_object);
        //   } else {
        //  requestHandler::sendResponse('Error fetching Data');
        requestHandler::sendResponse($to_return_object);
        //  }
    }

    public function actionSnapshotsStops() {

        //trigger the the request process property
        $request = requestHandler::processRequest();

        //get the request params from the request object
        $outputtype = $request->getRequestParams();

        //process the output format
        $to_return_object = $this->processOutputFormat('snapshotbus',$outputtype['outputformat']);


        //check if we have a valid object
        if (is_object($to_return_object) && isset($to_return_object)) {

            //return the response with the data    
            requestHandler::sendResponse($to_return_object);
        } else {
            //  requestHandler::sendResponse('Error fetching Data');
            requestHandler::sendResponse($to_return_object);
        }
    }

    /* function to get and format data upon the type
     * 
     * @param  $format : could be json,jqgrid or html
     * @return $output : object of arrays with data
     */

    public function processOutputFormat($querytype, $format) {

        switch ($format) {
            case 'json':
                $data = $this->getDataFromDb($querytype);
                $output = $this->formatToJson($data);
                return $output;
                break;

            case 'jqgrid':
                $data = $this->getDataFromDb($querytype);
                //   requestHandler::Log($data);
                $output = $this->formatToJgrid($data, $querytype);
                return $output;
                break;

            case 'html':

                break;
        }
    }

    /* function to format a set of arrayy and make it readable to google map plugin
     * 
     * @param  $data object of arrays
     * @return  $singleobject json object of data
     */

    public function formatToJson($data) {


        $displaylikear = array(
            "title" => "Snapshot Stops",
            'displaylike' => array(
                "Tooltips" => "Routes;Bearing;Location;Stop Name",
                "Type" => "Icon",
                "HotspotX" => 8,
                "HotspotY" => 16,
                "KeyField" => "Key",
                "Filters" => "Route;Stop Name;Make;Activity Status;Equipped",
                "RenderType" => "DespatcherStop",
                "RenderElements" => "Stop Name;Bearing;Routes;Vehicle Code;Make;Impact Count;Activity Status;Equipped",
                "ClickLink" => "ajax\/locationdetailspopup.php?location=<<Key>>",
                "Timestamp" => date("Y-m-d h:i:s"),
                ));

        foreach ($data as $singleobject) {

            $f = (array) $singleobject;
            $f['Equipped'] = "";
            $f['activity_status'] = "Unequipped";

            if (empty($f['build_code'])) {

                $f['Equipped'] = 'Unequipped';
                $n[] = $f;
            }
            if (!empty($f['Build code'])) {
                $f['Equipped'] = 'Equipped';
                $n[] = $f;
            }
            if (($f['Build code']) && ( ($f['last_active_hour']) > 1 || !($f['Message time']) )) {
                $f['activity_status'] = 'offline';
                $n[] = $f;
            }
            if (($f['Build code']) && ($f['Message time']) && ($f['last_active_hour']) <= 1) {
                $f['activity_status'] = 'online';
                $n[] = $f;
            }
            if (!($f['last_active_hour'])) {
                $f['last_active_hour'] = '0';
                $n[] = $f;
            }
        }

        $z = array('data' => $n);
        $f = array_merge($displaylikear, $z);
        $singleobject = (object) $f;
        return $singleobject;
    }

    /* maps a result set of data to a jqgrid format
     * 
     * @param  $data set of arrays
     * @return $finaljson  jqgrid formatted json 
     */

    public function formatToJgrid($data, $querytype) {

        //add and check specefic needed fields
        foreach ($data as $singleobject) {

            $f = (array) $singleobject;

            if ($querytype == 'timetablemonitor') {

                $fromdate = str_replace("-", "/", $f["day"]);

                $f['Id'] = "<a class='expandwindow' href='/viewer/iconnex/protected/extensions/reportico/run.php?xmlin=timetabbyid.xml&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=rti&MANUAL_id=" . $f['id'] . "&MANUAL_date_FROMDATE=" . $fromdate . "&MANUAL_date_TODATE=" . $fromdate . "' target='_blank'>&nbsp;&nbsp;</a>";
                $f['operating'] = "blank";

                if ($f['duty_no'] == "NODUTY") {

                    $f['duty_no'] = 'None';
                    $n[] = $f;
                }
                if ($f['runningno'] == "NOBLK") {

                    $f['duty_no'] = 'None';
                    $n[] = $f;
                }
            } else {
                $f['Equipped'] = "";
                $f['activity_status'] = "Unequipped";

                if (empty($f['build_code'])) {

                    $f['Equipped'] = 'Unequipped';
                    $n[] = $f;
                }
                if (!empty($f['Build code'])) {
                    $f['Equipped'] = 'Equipped';
                    $n[] = $f;
                }
                if (($f['Build code']) && ( ($f['last_active_hour']) > 1 || !($f['Message time']) )) {
                    $f['activity_status'] = 'offline';
                    $n[] = $f;
                }
                if (($f['Build code']) && ($f['Message time']) && ($f['last_active_hour']) <= 1) {
                    $f['activity_status'] = 'online';
                    $n[] = $f;
                }
            }
        }

        //get the array key for the first array
        //the array keys represent the columnnames
        $colnamesarray = array_keys($data[0]);

        //counting the columns names
        $countcolnames = count($colnamesarray);


        //loop through each array
        foreach ($n as $key => $row) {

            //fullfill the new array as the format needed
            $rows[] = array(
                "id" => $key,
                "cell" => array(
                    $row['route_code'],
                    $row['Trip'],
                    $row['start_time'],
                    $row['event_code'],
                    $row['day'],
                    $row['duty_no'],
                    $row['operator_code'],
                    $row['Service'],
                    $row['id'],
                    $row['operating'],
                )
            );
        }

        //build the required part of the json
        $gridmodel = array(
            "total" => 1,
            "page" => 1,
            "records" => 1000000,
            "rows" => $rows,
        );



        //build the required part of the json with the columne names
        for ($i = 0; $i < $countcolnames; $i++) {

            //fulfill a new array
            $colmodelarray[] = array(
                "name" => $colnamesarray[$i],
                "index" => $colnamesarray[$i],
                "editable" => false,
                "edittype" => "text",
                "sorttype" => "text",
                "stype" => "text",
                "jsonmap" => $colnamesarray[$i],
                "width" => "80",
            );
        }

        //if the query is timetable remove those rows
        if (!empty($colnamesarray['holiday_op'])) {
            unset($colnamesarray['org_working_op']);
            unset($colnamesarray['org_working_noop']);
            unset($colnamesarray['org_holiday_op']);
            unset($colnamesarray['org_holiday_noop']);
            unset($colnamesarray['special_days_op']);
            unset($colnamesarray['special_days_noop']);
        }

        //build the final object needed
        $finaljson = array(
            "JSON" => "success",
            "viewname" => "",
            "colmodel" => $colmodelarray,
            "colnames" => $colnamesarray,
            "minihide" => array(),
            "graphopt" => false,
            "buttons" => array(),
            "timestamp" => date("Y-m-d h:m:s"),
            "gridmodel" => $gridmodel,
        );



        return $finaljson;
    }

    /* function to route querytype with its type
     * 
     * 
     */

    public function getDataFromDb($querytype) {

        switch ($querytype) {
            case 'snapshotbus':
                return $this->getBusStopsFromDb();
                break;

            case 'timetablemonitor':
                return $this->getTtMonitorFromDb();
                break;
        }
    }

    public function getBusStopsFromDb() {

        //get the current username
        $curusername = yii::app()->user->id;

        $rows = yii::app()->db->createCommand()
                ->select("location_code as key, bay_no as Bay, description as Stop name, route_area_code as Area, latitude as Latitude, longitude, build_code as Build code, message_time as Message time, route.route_code as route, make as make, impact_count as impact_count, last_bootup as last_bootup, bootup_count as bootup_count, last_active_hour as  last_active_hour, last_active_day as last_active_day, routes as  routes, bearing ")
                ->from('snapshot_stop_status route')
                ->join('route_visibility', 'route.route_id = route_visibility.route_id')
                ->join('operator', 'operator.operator_id = route_visibility.operator_id')
                ->where('usernm =:username', array(':username' => $curusername))
                ->queryAll();

        if (!empty($rows)) {

            //send the result to the cache
         //   Yii::app()->cache->set('bussnapshots', $rows);

            //return the result set
            return $rows;
        } else {

            throw new Exception("no data retreived from the db");
        }
    }

    public function getTtMonitorFromDb() {

        //get the current username
        $curusername = yii::app()->user->id;

        $rows = yii::app()->db->createCommand("SELECT
            t_timetable.route_code as route_code, publish_tt.trip_no as Trip, publish_tt.runningno as  runningno, publish_tt.start_time as start_time, event.event_code as event_code, t_timetable.day as day, publish_tt.duty_no as duty_no, t_timetable.operator_code as operator_code, t_timetable.service_code as Service, publish_tt.pub_ttb_id as id,
etm_trip_no,
t_timetable.holiday_op,
t_timetable.holiday_noop,
t_timetable.org_working_op,
t_timetable.org_working_noop,
t_timetable.org_holiday_op,
t_timetable.org_holiday_noop,
t_timetable.special_days_op,
t_timetable.special_days_noop,
dest_long
 
FROM t_timetable, publish_tt,event,event_pattern, service_patt, destination
WHERE 1 = 1          
AND t_timetable.pub_ttb_id = publish_tt.pub_ttb_id
AND publish_tt.evprf_id = event_pattern.evprf_id
AND event_pattern.event_id = event.event_id  
AND t_timetable.event_id = event.event_id 
AND service_patt.service_id = publish_tt.service_id
and service_patt.rpat_orderby = 1
and destination.dest_id = service_patt.dest_id 
ORDER BY  t_timetable.day ASC, t_timetable.operator_code ASC, t_timetable.route_code ASC, event.event_code ASC, publish_tt.start_time ASC")
                //   ->select("SELECT t_timetable.route_code as route_code, publish_tt.trip_no as trip_no, publish_tt.runningno as runningno, publish_tt.start_time as start_time, event.event_code as event_code, t_timetable.day as day, publish_tt.duty_no as duty_no, t_timetable.operator_code as operator_code, t_timetable.service_code as  service_code, publish_tt.pub_ttb_id as  id,etm_trip_no,t_timetable.holiday_op,t_timetable.holiday_noop,t_timetable.org_working_op,t_timetable.org_working_noop,t_timetable.org_holiday_op,t_timetable.org_holiday_noop,t_timetable.special_days_op,t_timetable.special_days_noop,dest_long")
                //    ->from("t_timetable, publish_tt,event,event_pattern, service_patt, destination")
                //   ->where('1 =:criteria', array(':criteria' => '1'),array('AND','t_timetable.pub_ttb_id = publish_tt.pub_ttb_id','publish_tt.evprf_id = event_pattern.evprf_id','event_pattern.event_id = event.event_id','t_timetable.event_id = event.event_id','service_patt.service_id = publish_tt.service_id','service_patt.rpat_orderby = 1','destination.dest_id = service_patt.dest_id'))
                //    ->order("t_timetable.day ASC, t_timetable.operator_code ASC, t_timetable.route_code ASC, event.event_code ASC, publish_tt.start_time ASC")
                //   ->where
                ->queryAll();

        if (!empty($rows)) {

            //send the result to the cache
            //  Yii::app()->cache->set('timetablemonitor', $rows);
            //return the result set
            return $rows;
        } else {

            throw new Exception("no data retreived from the db");
        }
    }

}

?>

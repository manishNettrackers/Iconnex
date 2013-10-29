<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('CUR_DATE', date("Y-m-d"));

class userTimetableBuilder {

    function __construct($datefrom = CUR_DATE, $dateto = CUR_DATE, $timefrom = "00:00:00", $timeto = "23:59:59", $operatorid = "t_route.operator_id", $routeid = "service.route_id",$runningboard ,$dutyno) {

        //get the current username
        $curusername = yii::app()->user->id;

        try {

            $this->create_temp_times($timefrom, $timeto);

            $this->createTempDays($datefrom, $dateto, date('Y-m-d'), 0);

            $this->build_user_timetable($curusername, $timefrom, $timeto, $operatorid, $routeid,$runningboard ,$dutyno);
      
             } catch (Exception $e) {

            echo 'error creating tt temp table ' . $e->getMessage();
        }
    }

    public function build_user_timetable($curusername, $timefrom, $timeto, $operatorid, $routeid) {

        try {

            yii::app()->db->createCommand("CREATE TEMPORARY TABLE IF NOT EXISTS t_timetable ( day date, dayno integer,	 dtime DATE,route_code char(8),	 service_code char(14),	 route_id integer,	 operator_code char(8),	 service_id int,	 pub_ttb_id int,	 runningno char(5),	 event_code char(8),	 event_id int,	 over_midnight char(1),	 trip_no char(10),	 duty_no char(6),	 operator_id integer,	 start_time VARCHAR(5),	 holiday_op integer,	 holiday_noop integer,	 org_working_op integer,	 org_working_noop integer,	 org_holiday_op integer,	 org_holiday_noop integer,	 special_days_op integer,	 special_days_noop integer,	 start_dow integer,	 end_dow integer )")
                    ->execute();
            if ($operatorid <> 't_route.operator_id') {
               

                $sql = "insert into t_timetable 
                    SELECT t_days.day, t_days.dayno, t_days.dtime, route_code, service.description service_code,t_route.route_id, operator.operator_code operator_code, service.service_id, publish_tt.pub_ttb_id pub_ttb_id, publish_tt.runningno runningno, event.event_code event_code, event.event_id, publish_tt.over_midnight over_midnight, trip_no, duty_no, operator.operator_id, start_time, holiday_op, holiday_noop, org_working_op, org_working_noop, org_holiday_op, org_holiday_noop, special_days_op, special_days_noop, rpdy_start, rpdy_end 
                    FROM operator,route_visibility as t_route,service, t_days, publish_tt,event_pattern,event 
                
       WHERE 1 = 1 
       AND operator.operator_id = t_route.operator_id
       AND service.route_id = t_route.route_id       
           AND usernm = '$curusername' 
               AND operator.operator_id = $operatorid
               AND t_route.route_id IN( $routeid ) 
               and runningno IN( $runningboard )
               and dutyno IN ( $dutyno )
               AND publish_tt.service_id = service.service_id
               and publish_tt.evprf_id = event_pattern.evprf_id 
               and event_pattern.event_id = event.event_id 
               #and t_days.day between service.wef_date and service.wet_date 
               AND start_time between '$timefrom' and '$timeto'  ";


                $conn = yii::app()->db;
                $command = $conn->createCommand($sql);
                $datareader = $command->execute();

                error_log("query2 " . $command->getPdoStatement()->queryString);
            } else {
                $sql = "insert into t_timetable SELECT t_days.day, t_days.dayno, t_days.dtime, route_code, service.description service_code,t_route.route_id, operator.operator_code operator_code, service.service_id, publish_tt.pub_ttb_id pub_ttb_id, publish_tt.runningno runningno, event.event_code event_code, event.event_id, publish_tt.over_midnight over_midnight, trip_no, duty_no, operator.operator_id, start_time, holiday_op, holiday_noop, org_working_op, org_working_noop, org_holiday_op, org_holiday_noop, special_days_op, special_days_noop, rpdy_start, rpdy_end FROM operator,route_visibility as t_route,service, t_days, publish_tt,event_pattern,event 
                
       WHERE 1 = 1 AND usernm = '$curusername' AND operator.operator_id = $operatorid AND t_route.route_id IN( $routeid ) AND publish_tt.service_id = service.service_id and publish_tt.evprf_id = event_pattern.evprf_id and event_pattern.event_id = event.event_id and t_days.day between service.wef_date and service.wet_date AND start_time between '$timefrom' and '$timeto' LIMIT 1";


                $conn = yii::app()->db;
                $command = $conn->createCommand($sql);
                $datareader = $command->execute();
            }
            //  requesthandler::log($conn->getPdoStatement()->queryString);
        } catch (Exception $e) {

            echo 'Error creating temp timetable : ' . $e->getMessage();
        }
    }

    public function create_temp_times($timefrom, $timeto) {

        try {

            yii::app()->db->createCommand("CREATE TEMPORARY TABLE IF NOT EXISTS t_times ( from_time CHAR(8), to_time CHAR(8) )")
                    ->execute();
            yii::app()->db->createCommand("INSERT INTO t_times VALUES ( '$timefrom', '$timeto' )")
 
                    ->execute();
        } catch (Exception $e) {

            echo 'Error creating temp times table : ' . $e->getMessage();
        }
    }

    public function createTempDays($datefrom, $dateto, $dtime, $dayno) {

        try {

            yii::app()->db->createCommand("CREATE  TEMPORARY TABLE IF NOT EXISTS t_days  ( day DATE, dtime datetime, dayno INT )")
                    ->execute();

            if ($datefrom == CUR_DATE && $dateto == CUR_DATE) {

                YII::app()->db->createCommand("INSERT INTO t_days VALUES ( '$datefrom', '$dtime', '$dayno' )")
                        ->execute();
            } else {

                $days = new DatePeriod(
                                new DateTime($datefrom),
                                new DateInterval('P1D'),
                                new DateTime($dateto)
                );

                foreach ($days as $singleday) {

                    $day = $singleday->format('Y-m-d');
                    $weekday = $singleday->format('w');

                    YII::app()->db->createCommand("INSERT INTO t_days VALUES ( '$day',  '$day',  '$weekday' )")
                            ->execute();
                }
            }
        } catch (Exception $e) {

            echo 'Error creating temp days table : ' . $e->getMessage();
        }
    }

}

?>

<?php

/**
** TempCountdownStats
**
** Temp table used and created by the Prediction Delivery mechanism which will
** contain for a stop details of which vehicles, route and destinations we
** have predictions for. We use this information to know how many arrivals and how many
** of each route, destination etc we have so we can use the prediction parameters which
** limit number of countdowns sent to a sign eg by route/number of arrivals
** prediction parameters to decide which countdowns need to be sent
*
*/
class TempCountdown extends DataModel
{
    public $componentstmt = false;

    function __construct($connector)
    {
        $this->columns = array ( 

            "arr_no" => new DataModelColumn ( $this->connector,  "arr_no", "serial" ),
            "vehicle_id" => new DataModelColumn ( $this->connector,  "vehicle_id", "integer" ),
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer" ),
            "route_id" => new DataModelColumn ( $this->connector,  "route_id", "integer" ),
            "dest_id"  => new DataModelColumn ( $this->connector,  "dest_id", "integer" ),
            );

        $this->tableName = "t_countdowns";
        $this->tempTable = true;
        //$this->className = "TempCountdown";
        $this->keyColumns = array ( "arr_no" );

        parent::__construct($connector);
    }

    function checkPrediction ( $mode, $predictionParameters, $in_old_countdown, $in_new_countdown )
    {
        if ( $mode == "DELIVER" )
        {
            $this->arr_no = 0;
            $this->save();
        }

        if ( $mode == "NUMARRS" ) {

            $ct = $this->count(array("build_id"));
            if ( $ct >= $predictionParameters->max_arrivals ) 
                return "TOOMANY";
            else
                return "OK";
        }

        if ( $mode == "NUMARRSPERDEST" ) {
            $ct = $this->count(array("route_id", "dest_id", "build_id")) ;
            if ( $ct >= $predictionParameters->max_dest_arrivals ) 
                return "TOOMANY";
            else
                return "OK";
        }

        if ( $mode == "DUPVEH" ) {
            if ( $this->vehicle_id == 0 ) 
                return "OK";
            
            $ct = $this->count(array("vehicle_id", "build_id"));
            if ( $ct > 0 )
            {
                return "DUPVEH";
            }
            else
                return "OK";
        }
     
        if ( $mode == "HASCHANGEDENOUGH" ) {
            $changed = false;
            if ( !$changed ) {
                if ( $predictionParameters->countdown_dep_arr == "A" ) {
                    $eta_old_last_sent = DateTime::createFromFormat("Y-m-d H:i:s",$in_old_countdown->eta_last_sent);
                    $etd_old_last_sent = DateTime::createFromFormat("Y-m-d H:i:s",$in_old_countdown->etd_last_sent);
                    $eta_new_last_sent = DateTime::createFromFormat("Y-m-d H:i:s",$in_new_countdown->eta_last_sent);
                    $etd_new_last_sent = DateTime::createFromFormat("Y-m-d H:i:s",$in_new_countdown->etd_last_sent);
                    $tmp_interval = $eta_new_last_sent->getTimestamp() - $eta_old_last_sent->getTimestamp();
                } 
                else 
                {
                    $tmp_interval = $etd_new_last_sent->getTimestamp() - $eta_old_last_sent->getTimestamp();
                }
            
                if ( $predictionParameters->update_thresh_low > $tmp_interval || $predictionParameters->update_thresh_high < $tmp_interval ) {
                    //echo " => Tolerance allows countdown update ",
                        //$predictionParameters->update_thresh_low using "-<<<<<&", " < ",
                        //tmp_interval using "-<<<<<&", " < ",
                        //$predictionParameters->update_thresh_high using "-<<<<<&"
                    $changed = true;
                } else {
                    $this->arr_no = 0;
                    $this->add();
                }
            }

            if ( $changed ) {
                return "OK";
            } else {
                return "NOTCHANGED";
            }
        }

        // echo "INVALID SIGN_COUNTDOWN CODE"
        return "INVALID";
    }

}
?>

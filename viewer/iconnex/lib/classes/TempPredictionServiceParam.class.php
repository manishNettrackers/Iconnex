<?php

class TempPredictionServiceParam extends DataModel
{
    public $lastRefresh = false;
    public $refreshInterval = 3600;

    function __construct($connector = false)
    {
        $this->columns = array ( 
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "integer" ),
            "route_id"  => new DataModelColumn ( $this->connector,  "route_id", "integer" ),
            "day_of_week" => new DataModelColumn ( $this->connector,  "day_of_week", "integer" ),
            "wef_time" => new DataModelColumn ( $this->connector,  "wef_time", "datetimehourtosecond" ),
            "wet_time" => new DataModelColumn ( $this->connector,  "wet_time", "datetimehourtosecond" ),
            "max_arrivals" => new DataModelColumn ( $this->connector,  "max_arrivals", "integer" ),
            "max_dest_arrivals" => new DataModelColumn ( $this->connector,  "max_dest_arrivals", "integer" ),
            "autort_preempt" => new DataModelColumn ( $this->connector,  "autort_preempt", "interval" ),
            "pred_layover" => new DataModelColumn ( $this->connector,  "pred_layover", "char", 1 ),
            "pred_pub_after" => new DataModelColumn ( $this->connector,  "pred_pub_after", "integer" ),
            "disp_pub_after" => new DataModelColumn ( $this->connector,  "disp_pub_after", "integer" ),
            "display_window" => new DataModelColumn ( $this->connector,  "display_window", "integer" ),
            "countdown_dep_arr" => new DataModelColumn ( $this->connector,  "countdown_dep_arr", "char", 1 ),
            "delivery_mode" => new DataModelColumn ( $this->connector,  "delivery_mode", "char", 5 ),
            "update_thresh_low" => new DataModelColumn ( $this->connector,  "update_thresh_low", "integer" ),
            "update_thresh_high" => new DataModelColumn ( $this->connector,  "update_thresh_high", "integer" ),
            "loop_sleep" => new DataModelColumn ( $this->connector,  "loop_sleep", "integer" ),
            "disabled" => new DataModelColumn ( $this->connector,  "disabled", "char", 1 ),
            );

        $this->tableName = "t_prediction_service_param";
        $this->tempTable = true;
        $this->className = "TempPredictionServiceParam";
        $this->keyColumns = array ( "route_id" );

        parent::__construct($connector);
    }

    function runTask()
    {
        echo "Task $this->className has no runTask method defined.";
    }

    function buildTable ()
    {
        // Only build if required ( not built yet or due for build )
        $now = new DateTime();
        if ( $this->lastRefresh && $this->lastRefresh->getTimestamp() > $now->getTimestamp() - $this->refreshInterval )
            return;

        $this->lastRefresh = $now;
        $where_clause = "";

        echo "Building Prediction Parameter Route table\n";
        $this->dropTable();
        $this->createTable();

        /* Populate temp table with every route so we can set route specific parameters later
           from prediction_parameter table */
        $sql = "
		INSERT INTO ".$this->tableName."
                (
    		    operator_id,
    		    route_id,
                max_arrivals,
                max_dest_arrivals,
                autort_preempt,
                pred_pub_after,
                disp_pub_after,
    		    display_window,
    		    countdown_dep_arr,
    		    delivery_mode,
    		    update_thresh_low,
    		    update_thresh_high,
    		    loop_sleep )
    		SELECT UNIQUE a.operator_id, a.route_id, 
                    9,
                    9,
                    '00:30:00',
                    3600,
                    3600,
    		        0,
    		        'A',
    		        'RCA',
    		        0,
    		        0,
    		        30
            FROM route a, service b
            WHERE a.route_id = b.route_id
            AND TODAY BETWEEN wef_date AND wet_date
            ";
        $this->connector->executeSQL($sql);

        $predparam = new PredictionParameter($this->connector);
        $params = $predparam->selectAll("level", 
                    "location_id IS NULL
                    AND build_id IS NULL
                    AND wef_time IS NULL
                    AND wet_time IS NULL
                    AND day_of_week IS NULL
                    " );
        foreach ( $params as $row )
        {
            // Build update statement to set route specific parameters held in prediction_parameter table
            $field_clause = "";
            $value_clause = "";
            $field_ct = 0;

            $easyparams = array (
                "pred_layover" => "pred_layover",
                "max_arrivals" => "max_arrivals",
                "max_dest_arrivals" => "max_dest_arrivals",
                "autort_preempt" => "autort_preempt",
                "pred_pub_after" => "pred_pub_after",
                "disp_pub_after" => "disp_pub_after",
                "display_window" => "display_window",
                "countdown_dep_arr" => "countdown_dep_arr",
                "delivery_mode" => "delivery_mode",
                "update_thresh_low" => "update_thresh_low",
                "update_thresh_high" => "update_thresh_high",
                "display_window" => "display_window",
                "disabled" => "disabled"
                );

            foreach ( $easyparams as $k => $v )
            {
                if ( $row->$k ) {
                    if ( $field_ct > 0 ) {    
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct++;
                    $field_clause .= $v;
                    $value_clause .= "'".$row->$k."'";
                }
            }

            $where_clause = " WHERE 1 = 1";

            if ( $row->operator_id ) {
                $where_clause .= " AND operator_id = ". $row->operator_id;
            }

            if ( $row->route_id ) {
                $where_clause .= " AND route_id = ". $row->route_id;
            }

            if ( $row->location_id ) {
                $where_clause .= " AND location_id = ". $row->location_id;
            }

            if ( $row->day_of_week ) {
                $now = new DateTime();
                $nowdow = $now->format("w");
                if ( $nowdow == $row->day_of_week ) {
                    echo "IGNORING DOW SPECIFIER ". $row->day_of_week. " vs ". $nowdow."\n";
                    continue;
                }
            }

            if ( ( $row->wef_time AND !$row->wet_time ) ||
                ( $row->wef_time AND !$row->wet_time ) ) {
                echo "INVALID DCD EFFECTIVE TIMES". $row->wef_time. "/". $row->wetTime."\n";
                continue;
            }

            if ( $row->wef_time AND $row->wet_time ) {
                $now = new DateTime();
                $from = DateTime::createFromFormat("H:i:s", $row->wefTime);
                $to = DateTime::createFromFormat("H:i:s", $row->wetTime);
                $now_hhmmss = $now->format("His");
                $from_hhmmss = $from->format("His");
                $to_hhmmss = $to->format("His");
                if ( $now_hhmmss < $from_hhmmss || $now_hhmmss > $to_hhmmss ) {
                    echo "Current ". $now_hhmmss. " OUTSIDE ". $from_hhmmss. "-". $to_hhmmss."\n";
                    continue;
                }
            }


            $sql = "UPDATE ".$this->tableName." SET ( ". $field_clause. ") = ( ".
                    $value_clause. ")". $where_clause;

            $status = $this->connector->executeSQL($sql);
            if ( !$status )
            {
                echo "Unable to set Route Level Prediciton Parameter\n";
                return false;
            }
        }
        return true;
    }
}

?>

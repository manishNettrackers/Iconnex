<?php

class TempPredictionLocationParam extends DataModel
{
    public $lastRefresh = false;
    public $refreshInterval = 3600;

    function __construct($connector = false)
    {
        $this->columns = array (
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "integer"  ),
            "route_id" => new DataModelColumn ( $this->connector,  "route_id", "integer"  ),
            "location_id" => new DataModelColumn ( $this->connector,  "location_id", "integer"  ),
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer"  ),
            "display_type" => new DataModelColumn ( $this->connector,  "display_type", "char", 1 ),
            "day_of_week" => new DataModelColumn ( $this->connector,  "day_of_week", "integer"  ),
            "wef_time" => new DataModelColumn ( $this->connector,  "wef_time", "datetime" ),
            "wet_time" => new DataModelColumn ( $this->connector,  "wet_time", "datetime" ),
            "max_arrivals" => new DataModelColumn ( $this->connector,  "max_arrivals", "integer"  ),
            "max_dest_arrivals" => new DataModelColumn ( $this->connector,  "max_dest_arrivals", "integer"  ),
            "pred_pub_after" => new DataModelColumn ( $this->connector,  "pred_pub_after", "integer"  ),
            "disp_pub_after" => new DataModelColumn ( $this->connector,  "disp_pub_after", "integer"  ),
            "display_window" => new DataModelColumn ( $this->connector,  "display_window", "integer"  ),
            "countdown_dep_arr" => new DataModelColumn ( $this->connector,  "countdown_dep_arr", "char", 1  ),
            "delivery_mode" => new DataModelColumn ( $this->connector,  "delivery_mode", "char", 5  ),
            "update_thresh_low" => new DataModelColumn ( $this->connector,  "update_thresh_low", "integer"  ),
            "update_thresh_high" => new DataModelColumn ( $this->connector,  "update_thresh_high", "integer"  ),
            "loop_sleep" => new DataModelColumn ( $this->connector,  "loop_sleep", "integer"  ),
            "disabled" => new DataModelColumn ( $this->connector,  "disabled", "char", 1 ),
            );

        $this->tableName = "";
        $this->tableName = "t_prediction_param";
        $this->tempTable = true;

        parent::__construct($connector);
    }

    function createPostIndexes()
    {
        $sql = "CREATE INDEX i_".$this->tableName." ON ".$this->tableName." (route_id)";
        $ret = $this->connector->executeSQL($sql);

        if ( $ret )
        {
            $sql = "CREATE INDEX i_".$this->tableName."2 ON ".$this->tableName." (build_id)";
            $ret = $this->connector->executeSQL($sql);
        }

        if ( $ret )
        {
            $sql = "CREATE INDEX i_".$this->tableName."3 ON ".$this->tableName." (location_id)";
            $ret = $this->connector->executeSQL($sql);
        }

        return $ret;
    }

    function buildTable ()
    {
        // Only build if required ( not built yet or due for build )
        $now = new DateTime();
        if ( $this->lastRefresh && $this->lastRefresh->getTimestamp() > $now->getTimestamp() - $this->refreshInterval )
            return;

        $this->lastRefresh = $now;
        echo "Building Temp Display Point table\n";
        $this->dropTable();
        $this->createTable();

        $sql =
            "
		    INSERT INTO t_prediction_param
                (
    		    operator_id,
    		    route_id,
                location_id,
                build_id,
                display_type,
                max_arrivals,
                max_dest_arrivals,
                pred_pub_after,
                disp_pub_after,
    		    display_window,
    		    countdown_dep_arr,
    		    delivery_mode,
    		    update_thresh_low,
    		    update_thresh_high,
    		    loop_sleep )
    		SELECT UNIQUE a.operator_id, a.route_id,
                d.location_id, d.build_id,
                d.display_type,
                    9,
                    9,
                    3600,
                    3600,
    		        0,
    		        'A',
    		        'RCA',
    		        0,
    		        0,
    		        30
            FROM route a, service b, service_patt c, display_point d, location e
            WHERE a.route_id = b.route_id
            AND b.service_id = c.service_id
            AND c.location_id = d.location_id
            AND TODAY BETWEEN wef_date AND wet_date
            AND c.location_id = e.location_id
            ";

        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to phase 1 populate  t_prediction_param\n";
            return false;
        }

        $predparam = new PredictionParameter($this->connector);
        $preds = $predparam->selectAll();

        foreach ( $preds as $k => $pred )
        {
                $field_clause = "";
                $value_clause = "";
                $field_ct = 0;

                if ( $pred->max_arrivals ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "max_arrivals";
                    $value_clause .= $pred->max_arrivals;
                }

                if ( $pred->max_dest_arrivals ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "max_dest_arrivals";
                    $value_clause .= $pred->max_dest_arrivals;
                }

                if ( $pred->pred_pub_after ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "pred_pub_after";
                    $value_clause .= $pred->pred_pub_after;
                }

                if ( $pred->disp_pub_after ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "disp_pub_after";
                    $value_clause .= $pred->disp_pub_after;
                }

                if ( $pred->display_window ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "display_window";
                    $value_clause .= $pred->display_window;
                }

                if ( $pred->countdown_dep_arr ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "countdown_dep_arr";
                    $value_clause .= "'". trim($pred->countdown_dep_arr). "'";
                }

                if ( $pred->delivery_mode ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "delivery_mode";
                    $value_clause .= "'". trim($pred->delivery_mode). "'";
                }

                if ( $pred->update_thresh_low ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "update_thresh_low";
                    $value_clause .= $pred->update_thresh_low;
                }

                if ( $pred->update_thresh_high ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "update_thresh_high";
                    $value_clause .= $pred->update_thresh_high;
                }

                if ( $pred->display_window ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "display_window";
                    $value_clause .= $pred->display_window;
                }

                if ( $pred->disabled ) {
                    if ( $field_ct > 0 ) {
                        $field_clause .= ",";
                        $value_clause .= ",";
                    }
                    $field_ct = $field_ct + 1;
                    $field_clause .= "disabled";
                    $value_clause .= "'". trim($pred->disabled). "'";
                }

                // if ( $LENGTH ( $field_clause ) = 0 ) {
                    // echo "NOT SETTING";
                // }

                $where_clause = " WHERE 1 = 1";

                if ( $pred->operator_id ) {
                    $where_clause = $where_clause .
                        " AND operator_id = ". $pred->operator_id;
                }

                if ( $pred->route_id ) {
                    $where_clause = $where_clause .
                        " AND route_id = ". $pred->route_id;
                }

                if ( $pred->location_id ) {
                    $where_clause = $where_clause .
                        " AND location_id = ". $pred->location_id;
                }

                if ( $pred->build_id ) {
                    $where_clause = $where_clause .
                        " AND build_id = ". $pred->build_id;
                }

                if ( $pred->day_of_week ) {
                    $now = new DateTime();
                    $nowdow = $now->format("w");
                    if ( $nowdow == $pred->day_of_week ) {
                        echo "IGNORING DOW SPECIFIER ". $pred->day_of_week. " vs ". $nowdow."\n";
                        continue;
                    }
                }

                if ( ( $pred->wef_time && !$pred->wet_time ) ||
                    ( $pred->wef_time && !$pred->wet_time ) ) {
                    // echo "INVALID DCD EFFECTIVE TIMES". $pred->wef_time. "/". $pred->wet_time
                    continue;
                }

                if ( $pred->wef_time AND $pred->wet_time ) {
                    $now = new DateTime();
                    $from = DateTime::createFromFormat("H:i:s", $pred->wef_time);
                    $to = DateTime::createFromFormat("H:i:s", $pred->wef_time);
                    $now_hhmmss = $now->format("His");
                    $from_hhmmss = $from->format("His");
                    $to_hhmmss = $to->format("His");
                    if ( $now_hhmmss < $from_hhmmss || $now_hhmmss > $to_hhmmss ) {
                        echo "Current ". $now_hhmmss. " OUTSIDE ". $from_hhmmss. "-". $to_hhmmss."\n";
                        continue;
                    }
                }


                if ( strlen($field_clause) == 0 ) {
                    // echo "IGNORED NOTHING TO SET"
                    continue;
                }

                $sql = "UPDATE ".$this->tableName." SET ( ". $field_clause . ") = ( ".
                        $value_clause . ")". $where_clause;

                if ( !$this->connector->executeSQL($sql) )
                {
                    echo "Failed to set location specific prediction parameter\n";
                    return false;
                }
        }


        $this->createPostIndexes();


        return true;
    }

}
?>

<?php

class PredictionParameter extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array ( 
            "dcd_id" => new DataModelColumn ( $this->connector,  "dcd_id", "serial" ),
            "level" => new DataModelColumn ( $this->connector,  "level", "integer", false, false ),
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "integer" ),
            "route_id" => new DataModelColumn ( $this->connector,  "route_id", "integer" ),
            "location_id" => new DataModelColumn ( $this->connector,  "location_id", "integer" ),
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer" ),
            "day_of_week" => new DataModelColumn ( $this->connector,  "day_of_week", "integer" ),
            "wef_time" => new DataModelColumn ( $this->connector,  "wef_time", "datetimehourtosecond" ),
            "wet_time" => new DataModelColumn ( $this->connector,  "wet_time", "datetimehourtosecond" ),
            "max_arrivals" => new DataModelColumn ( $this->connector,  "max_arrivals", "integer" ),
            "max_dest_arrivals" => new DataModelColumn ( $this->connector,  "max_dest_arrivals", "integer" ),
            "autort_preempt" => new DataModelColumn ( $this->connector,  "autort_preempt", "datetimehourtosecond" ),
            "pred_layover" => new DataModelColumn ( $this->connector,  "pred_layover", "char", 1 ),
            "pred_pub_after" => new DataModelColumn ( $this->connector,  "pred_pub_after", "integer" ),
            "disp_pub_after" => new DataModelColumn ( $this->connector,  "disp_pub_after", "integer" ),
            "display_window" => new DataModelColumn ( $this->connector,  "display_window", "integer" ),
            "countdown_dep_arr" => new DataModelColumn ( $this->connector,  "countdown_dep_arr", "char", 1 ),
            "delivery_mode" => new DataModelColumn ( $this->connector,  "delivery_mode", "char", 5 ),
            "update_thresh_low"  => new DataModelColumn ( $this->connector,  "update_thresh_low", "integer" ),
            "update_thresh_high"  => new DataModelColumn ( $this->connector,  "update_thresh_high", "integer" ),
            "loop_sleep"  => new DataModelColumn ( $this->connector,  "loop_sleep", "integer" ),
            "disabled"  => new DataModelColumn ( $this->connector,  "disabled", "char", 1 ),
            );

        $this->tableName = "dcd_param";
        $this->dbspace = "centdbs";
        $this->keyColumns = array ( "param_id" );

        parent::__construct($connector, $initialiserArray);

    }

    function populateTable()
    {
        $sql = "INSERT INTO dcd_param ( dcd_id, level, display_window, countdown_dep_arr, delivery_mode, update_thresh_low, update_thresh_high, loop_sleep ) 
                    VALUES ( 0, 1, 1800, 'A', 'RCA', -10, 10, 10 )";
        $this->connector->executeSQL($sql);
    }
}

?>

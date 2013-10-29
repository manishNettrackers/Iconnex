<?php

class SnapshotBoardStatus extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array ( 
            "timetable_id" => new DataModelColumn ( $this->connector,  "timetable_id", "integer" ),
            "vehicle_id" => new DataModelColumn ( $this->connector,  "vehicle_id", "integer" ),
            "active_status" => new DataModelColumn ( $this->connector,  "active_status", "char", 10 ),
            "scheduled_start" => new DataModelColumn ( $this->connector,  "scheduled_start", "datetime" ),
            "pub_ttb_id" => new DataModelColumn ( $this->connector,  "pub_ttb_id", "integer" ),
            "operation_date" => new DataModelColumn ( $this->connector,  "operation_date", "date" ),
            "over_midnight" => new DataModelColumn ( $this->connector,  "over_midnight", "integer" ),
            "operator_code" => new DataModelColumn ( $this->connector,  "operator_code", "char", 10 ),
            "route_code" => new DataModelColumn ( $this->connector,  "route_code", "char", 10 ),
            "route_id" => new DataModelColumn ( $this->connector,  "route_id", "integer" ),
            "service_code" => new DataModelColumn ( $this->connector,  "service_code", "char", 10 ),
            "start_time" => new DataModelColumn ( $this->connector,  "start_time", "datetimehourtoseconds" ),
            "event_code" => new DataModelColumn ( $this->connector,  "event_code", "char", 10 ),
            "trip_no" => new DataModelColumn ( $this->connector,  "trip_no", "char", 10 ),
            "runningno" => new DataModelColumn ( $this->connector,  "runningno", "char", 10 ),
            "duty_no" => new DataModelColumn ( $this->connector,  "duty_no", "char", 10 ),
            "next_duty" => new DataModelColumn ( $this->connector,  "next_duty", "char", 10 ),
            "next_duty_time" => new DataModelColumn ( $this->connector,  "next_duty_time", "datetimehourtoseconds" ),
            "act_veh" => new DataModelColumn ( $this->connector,  "act_veh", "char", 10 ),
            "operation_date" => new DataModelColumn ( $this->connector,  "operation_date", "date" ),
            "trip_status" => new DataModelColumn ( $this->connector,  "trip_status", "char", 1 ),
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "integer" ),
            "act_start" => new DataModelColumn ( $this->connector,  "act_start", "char", 10 ),
            "mod_type" => new DataModelColumn ( $this->connector,  "mod_type", "char", 1 ),
            "mod_status" => new DataModelColumn ( $this->connector,  "mod_status", "char", 1 ),
            "journey_from" => new DataModelColumn ( $this->connector,  "journey_from", "datetime" ),
            "journey_to" => new DataModelColumn ( $this->connector,  "journey_to", "datetime" ),
            "act_sched" => new DataModelColumn ( $this->connector,  "act_sched", "integer" ),
            "arc_sched" => new DataModelColumn ( $this->connector,  "arc_sched", "integer" ),
            "diversion" => new DataModelColumn ( $this->connector,  "diversion", "integer" ),
            "alloc_vehcode" => new DataModelColumn ( $this->connector,  "alloc_vehcode", "char", 10 ),
            "pub_start" => new DataModelColumn ( $this->connector,  "pub_start", "datetime" ),
            "pub_end" => new DataModelColumn ( $this->connector,  "pub_end", "datetime" ),
            "rtpi_start" => new DataModelColumn ( $this->connector,  "rtpi_start", "datetime" ),
            "rtpi_end" => new DataModelColumn ( $this->connector,  "rtpi_end", "datetime" ),
            "performance" => new DataModelColumn ( $this->connector,  "performance", "char", 10 ),
            "lateness" => new DataModelColumn ( $this->connector,  "lateness", "integer" ),
            "lateness_min" => new DataModelColumn ( $this->connector,  "lateness_min", "integer" ),
            "real_lateness" => new DataModelColumn ( $this->connector,  "real_lateness", "interval" ),
            "row_changed" => new DataModelColumn ( $this->connector,  "row_changed", "datetime" ),
            "row_status" => new DataModelColumn ( $this->connector,  "row_status", "char", "10" ),
            "current_status" => new DataModelColumn ( $this->connector,  "current_status", "char", "10" )
            );

        $this->tableName = "snapshot_board_status";
        $this->dbspace = "centdbs";
        $this->keyColumns = array ( "timetable_id", "vehicle_id" );

        parent::__construct($connector);
    }

    function createIndexes()
    {
        $sql = "CREATE INDEX ix_snap_board on $this->tableName ( timetable_id );"; $ret = $this->connector->executeSQL($sql);
        $sql = "CREATE INDEX ix_snap_board1 on $this->tableName ( operator_code );"; $ret = $this->connector->executeSQL($sql);
        $sql = "CREATE INDEX ix_snap_board2 on $this->tableName ( route_id );"; $ret = $this->connector->executeSQL($sql);
        $sql = "CREATE INDEX ix_snap_board3 on $this->tableName ( trip_no );"; $ret = $this->connector->executeSQL($sql);
        $sql = "CREATE INDEX ix_snap_board4 on $this->tableName ( runningno );"; $ret = $this->connector->executeSQL($sql);
        $sql = "CREATE INDEX ix_snap_board5 on $this->tableName ( duty_no );"; $ret = $this->connector->executeSQL($sql);
        $sql = "CREATE INDEX ix_snap_board6 on $this->tableName ( vehicle_id );"; $ret = $this->connector->executeSQL($sql);

        return $ret;
    }

    /*
    ** Flag journey as removed
    */
    function flag_removed_journey ( $connector, $timetable_id, $vehicle_id )
    {
        $sql = "update snapshot_board_status set ( row_status, row_changed, current_status ) = ( 'DELETED', CURRENT, 'DELETED' )
                        where timetable_id = ".$timetable_id." AND vehicle_id = ".$vehicle_id;
        $this->connector->executeSQL($sql);
    }

}

?>

<?php
/**
* UnitStatusETM
*
* Datamodel for table unit_status_rt
*
*/

class UnitStatusETM extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "build_id" => new DataModelColumn ( $this->connector, "build_id", "integer" ),
            "unit_time" => new DataModelColumn ( $this->connector, "unit_time", "datetime" ),
            "etm_time" => new DataModelColumn ( $this->connector, "etm_time", "datetime" ),
            "etm_route" => new DataModelColumn ( $this->connector, "etm_route", "char", 8 ),
            "etm_running_no" => new DataModelColumn ( $this->connector, "etm_running_no", "char", 6 ),
            "etm_duty_no" => new DataModelColumn ( $this->connector, "etm_duty_no", "char", 6 ),
            "etm_trip_no" => new DataModelColumn ( $this->connector, "etm_trip_no", "char", 6 ),
            "etm_direction" => new DataModelColumn ( $this->connector, "etm_direction", "char", 4 ),
            "etm_status" => new DataModelColumn ( $this->connector, "etm_status", "char", 1 ),
            "fault_status" => new DataModelColumn ( $this->connector, "fault_status", "char", 1 ),
            "fault_time" => new DataModelColumn ( $this->connector, "fault_time", "datetime" ),
            "route_action" => new DataModelColumn ( $this->connector, "route_action", "integer" ),
            "route_time" => new DataModelColumn ( $this->connector, "route_time", "datetime" ),
            "route_status" => new DataModelColumn ( $this->connector, "route_status", "char", 1 ),
            "employee_id" => new DataModelColumn ( $this->connector, "employee_id", "integer" ),
            );

        $this->tableName = "unit_status_rt";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("build_id");
        parent::__construct($connector, $initialiserArray);
    }


    /*
    ** Stores latest vehicle etm data
    */
    function storeETMStatus($item, $journeydetails, $status)
    {

        $etmstatus = new UnitStatusETM($this->connector);
        $etmstatus->build_id = $item->unit_build->build_id;

        $now = new DateTime();

        $driver = new Driver($this->connector);
        $driver->operator_id = $item->unit_build->operator_id;
        $driver->employee_code = $journeydetails->driver_code;
        if ( !$driver->load(array("operator_id", "employee_code") ) )
        {
            $driver->employee_id = 0;
            $driver->operator_id = $item->unit_build->operator_id;
            $driver->employee_code = $journeydetails->driver_code;
            $driver->fullname = "Unknown";
            $driver->add();
        }

        $this->build_id = $etmstatus->build_id;
        $this->unit_time =  $now->format("Y-m-d H:i:s");
        $this->etm_time = $now->format("Y-m-d H:i:s");
        $this->etm_route = $journeydetails->service_code;
        $this->etm_running_no = $journeydetails->running_board;
        $this->etm_duty_no = $journeydetails->duty_number;
        $this->etm_trip_no = $journeydetails->journey_number;
        $this->etm_direction = $journeydetails->direction;
        $this->etm_status = "A";
        $this->fault_status = false;
        $this->fault_time = false;
        $this->route_action = $status;
        $this->route_time = $now->format("Y-m-d H:i:s");
        $this->route_status = "A";
        $this->employee_id = $driver->employee_id;
        
        if ( !$etmstatus->load(array("build_id")) )
        {
            $this->add();
        }
        else
        {
            $this->save();
        }

        $this->dump();
    }

}
?>

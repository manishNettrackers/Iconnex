<?php

require_once("DataModel.class.php");
require_once("Operator.class.php");

class Vehicle extends DataModel
{
    const UNKNOWN_ERROR = -1;
    const NO_ERROR = 0;
    const UNKNOWN_OPERATOR = 2;
    const UNKNOWN_VEHICLE = 3;
    const UNKNOWN_BUILD = 4;

    private $log;

    public $build_code = NULL;
    public $error_status = UNKNOWN_ERROR;

    function __construct($connector = false, $initialiserArray = false)
    {
        $this->log = Logger::getLogger(__CLASS__);

        $this->columns = array (
            "vehicle_id" => new DataModelColumn ( $this->connector,  "vehicle_id", "serial", false),
            "vehicle_code" => new DataModelColumn ( $this->connector,  "vehicle_code", "char", 10),
            "vehicle_type_id" => new DataModelColumn ( $this->connector,  "vehicle_type_id", "integer", false),
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "integer", false),
            "vehicle_reg" => new DataModelColumn ( $this->connector,  "vehicle_reg", "char", 10),
            "orun_code" => new DataModelColumn ( $this->connector,  "orun_code", "char", 10),
            "vetag_indicator" => new DataModelColumn ( $this->connector,  "vetag_indicator", "char", 1),
            "modem_addr" => new DataModelColumn ( $this->connector,  "modem_addr", "smallint"),
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer"),
            "wheelchair_access" => new DataModelColumn ( $this->connector,  "wheelchair_access", "integer", false)
            );
        $this->tableName = "vehicle";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("vehicle_id");

        parent::__construct($connector, $initialiserArray);
    }

    function initialiseByOperatorAndVehicle($operator_code, $vehicle_code)
    {
        global $rtpiconnector;

        $this->operator_code = $operator_code;
        $this->vehicle_code = $vehicle_code;

        $operator = new Operator($rtpiconnector);
        $operator->operator_code = $operator_code;

        if (!$operator->load(array($operator_code)))
        {
            $this->log->error("initialiseByOperatorAndVehicle() Failed to find operator $operator_code");
            $this->error_status = Vehicle::UNKNOWN_OPERATOR;
            return false;
        }
        $this->operator_id = $operator->operator_id;

        $vehicle = new Vehicle($rtpiconnector);
        $vehicle->operator_id = $operator->operator_id;
        $vehicle->vehicle_code = $vehicle_code;
        if (!$vehicle->load(array("operator_id", "vehicle_code")))
        {
            $this->log->error("initialiseByOperatorAndVehicle() Failed to find vehicle $vehicle_code for operator $operator_code");
            $this->error_status = Vehicle::UNKNOWN_VEHICLE;
            return false;
        }

        $this->vehicle_id = $vehicle->vehicle_id;
        $this->build_id = $vehicle->build_id;

        $unit_build = new UnitBuild($rtpiconnector);
        $unit_build->build_id = $this->build_id;
        if (!$unit_build->load())
        {
            $this->log->error("initialiseByOperatorAndVehicle() Failed to load UnitBuild with build_id " . $unit_build->build_id);
            $this->error_status = Vehicle::UNKNOWN_BUILD;
            return false;
        }
        $this->build_code = $unit_build->build_code;

        $this->error_status = NO_ERROR;
        return true;
    }
}

?>

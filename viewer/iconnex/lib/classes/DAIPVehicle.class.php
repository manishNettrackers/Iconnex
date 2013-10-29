<?php

class DAIPVehicle extends Vehicle
{
    public $sessionVehicleIdentifier = 0;

    function initialiseBySVID($svid)
    {
        global $rtpiconnector;

        $this->sessionVehicleIdentifier = $svid;

        $sql = "SELECT vehicle.vehicle_id, vehicle.operator_id, operator.operator_code, unit_build.build_code
            FROM vehicle_session, vehicle, operator, unit_build
            WHERE vehicle_session.vehicle_id = vehicle.vehicle_id
            AND operator.operator_id = vehicle.operator_id
            AND unit_build.build_id = vehicle.build_id
            AND vehicle_session.session_id = $svid";

        $ret = $rtpiconnector->fetch1SQL($sql);

        if (!$ret)
        {
            echo "Failed to find vehicle for svid $svid\n";
            return false;
        }

        $this->vehicle_id = $ret["vehicle_id"];
        $this->operator_id = $ret["operator_id"];
        $this->operator_code = $ret["operator_code"];
        $this->build_code = $ret["build_code"];

        return true;
    }

    function newSessionVehicleIdentifier()
    {
        global $rtpiconnector;
        global $sessionVehicleID;

        $sessionVehicleID++;

        $vehicle_session = new VehicleSession($rtpiconnector);
        $vehicle_session->vehicle_id = $this->vehicle_id;
        if (!$vehicle_session->load())
        {
            $vehicle_session->session_id = $sessionVehicleID;
            $vehicle_session->add();
        }
        else
        {
            $vehicle_session->session_id = $sessionVehicleID;
            $vehicle_session->save();
        }

        $this->sessionVehicleIdentifier = $sessionVehicleID;
        return $this->sessionVehicleIdentifier;
    }

    /*
     * @brief Gets the session id currently associated with a vehicle
     */
    function getSessionIdByVehicle($vehicle_code)
    {
        global $rtpiconnector;
        global $sessionVehicleID;

        $sql = "SELECT session_id 
            FROM vehicle 
            JOIN vehicle_session ON vehicle.vehicle_id = vehicle_session.vehicle_id
            JOIN unit_status ON vehicle.build_id = unit_status.build_id
            AND message_time > CURRENT - 20 units minute
            AND vehicle_code = '".$vehicle_code."'";
        $ret = $rtpiconnector->fetch1SQL($sql);
        if ($ret)
            return $ret["session_id"];

        return false;
    }
}

?>

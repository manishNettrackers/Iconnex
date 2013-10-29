<?php
/**
* Driver
*
* Datamodel for table employee
*
*/

class Driver extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "operator_id" => new DataModelColumn ( $this->connector, "operator_id", "integer" ),
            "employee_id" => new DataModelColumn ( $this->connector, "employee_id", "serial" ),
            "employee_code" => new DataModelColumn ( $this->connector, "employee_code", "char", 8 ),
            "fullname" => new DataModelColumn ( $this->connector, "fullname", "char", 30 ),
            "orun_code" => new DataModelColumn ( $this->connector, "orun_code", "char", 10 ),
            );

        $this->tableName = "employee";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("employee_id");
        parent::__construct($connector, $initialiserArray);

    }
}
?>

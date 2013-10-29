<?php

require_once("DataModel.class.php");

class Operator extends DataModel
{
    public $id = NULL;
    public $code = NULL;

    function __construct($connector = false)
    {
        $this->columns = array (
            "operator_id" => new DataModelColumn ( $this->connector,  "operator_id", "serial", false, false),
            "operator_code" => new DataModelColumn ( $this->connector,  "operator_code", "char", 8, false),
            "legal_name" => new DataModelColumn ( $this->connector,  "legal_name", "char", 48),
            "address01" => new DataModelColumn ( $this->connector,  "address01", "char", 20),
            "address02" => new DataModelColumn ( $this->connector,  "address02", "char", 20),
            "address03" => new DataModelColumn ( $this->connector,  "address03", "char", 20),
            "address04" => new DataModelColumn ( $this->connector,  "address04", "char", 20),
            "short_name" => new DataModelColumn ( $this->connector,  "short_name", "char", 24),
            "loc_prefix" => new DataModelColumn ( $this->connector,  "loc_prefix", "char", 3, false),
            "tel_travel" => new DataModelColumn ( $this->connector,  "tel_travel", "char", 12),
            "tel_enquiry" => new DataModelColumn ( $this->connector,  "tel_enquiry", "char", 12),
            );
        $this->tableName = "operator";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("operator_id");

        parent::__construct($connector);
    }

    function getOperator($operator)
    {
        global $rtpiconnector;

        $sql = "SELECT operator_id
                FROM operator
                WHERE operator.operator_code = '$operator'";
        echo $sql . "\n";
        $result = $rtpiconnector->fetch1SQL($sql);

        if (!$result)
        {
            $this->id = $this->add($operator_code);
            echo "Added operator with id " . $this->id . "\n";
        }
        else
        {
            $this->id = $result["OPERATOR_ID"];
            echo "Found operator with id " . $this->id . "\n";
        }
    }

    function add($operator_code, $vehicle_code)
    {
        $operator = new operator($operator_code);

        $legal_name = $operator_code;
        $address01 = "";
        $address02 = "";
        $address03 = "";
        $address04 = "";
        $short_name = $operator_code;
        $loc_prefix = $operator_code;
        $tel_travel = "";
        $tel_enquiry = "";

        $sql = "INSERT into operator
                VALUES (0, '$operator_code', 
                '$legal_name',
                '$address01',
                '$address02',
                '$address03',
                '$address04',
                '$short_name',
                '$loc_prefix',
                '$tel_travel',
                '$tel_enquiry')";

        echo $sql . "\n";
        $operator_id = 1;

        return $operator_id;
    }
}

?>

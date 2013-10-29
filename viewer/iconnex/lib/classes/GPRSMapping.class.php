<?php
/**
* GPRSMapping
*
* Datamodel for table gprs_mapping
*
*/

class GPRSMapping extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "build_id" => new DataModelColumn ( $this->connector, "build_id", "integer" , false, false ),
            "ip_address" => new DataModelColumn ( $this->connector, "ip_address", "char", 20 ),
            "connect_date" => new DataModelColumn ( $this->connector, "connect_date", "datetime" ),
            );

        $this->tableName = "gprs_mapping";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("build_id");
        parent::__construct($connector, $initialiserArray);

    }
}
?>

<?php
/**
* Destination
*
* Datamodel for table destination
*
*/

class Destination extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "dest_id" => new DataModelColumn ( $this->connector, "dest_id", "serial" ),
            "operator_id" => new DataModelColumn ( $this->connector, "operator_id", "integer" , false, false ),
            "dest_code" => new DataModelColumn ( $this->connector, "dest_code", "char", 50 ),
            "dest_long" => new DataModelColumn ( $this->connector, "dest_long", "char", 50 ),
            "dest_short1" => new DataModelColumn ( $this->connector, "dest_short1", "char", 20 ),
            "terminal_text" => new DataModelColumn ( $this->connector, "terminal_text", "char", 20 ),
            "display_text" => new DataModelColumn ( $this->connector, "display_text", "char", 20 ),
            );

        $this->tableName = "destination";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("dest_id");
        parent::__construct($connector, $initialiserArray);

    }
}
?>

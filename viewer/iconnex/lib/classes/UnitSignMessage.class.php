<?php
/**
* UnitSignMessage
*
* Datamodel for table dcd_message_loc
*
*/

class UnitSignMessage extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "message_id" => new DataModelColumn ( $this->connector, "message_id", "integer", false, false ),
            "build_id" => new DataModelColumn ( $this->connector, "build_id", "integer" , false, false ),
            "creation_time" => new DataModelColumn ( $this->connector, "creation_time", "datetime" ),
            "display_time" => new DataModelColumn ( $this->connector, "display_time", "datetime" ),
            "expiry_time" => new DataModelColumn ( $this->connector, "expiry_time", "datetime" ),
            "hold_time" => new DataModelColumn ( $this->connector, "hold_time", "integer" ),
            "interleave_mode" => new DataModelColumn ( $this->connector, "interleave_mode", "char", 10 ),
            "display_style" => new DataModelColumn ( $this->connector, "display_style", "char", 10 ),
            "activity_mode" => new DataModelColumn ( $this->connector, "activity_mode", "char", 1 , false ),
            "message_sent" => new DataModelColumn ( $this->connector, "message_sent", "datetime" ),
            "display_flag" => new DataModelColumn ( $this->connector, "display_flag", "smallint" ),
            "received" => new DataModelColumn ( $this->connector, "received", "datetime" ),
            "bundled_with" => new DataModelColumn ( $this->connector, "bundled_with", "integer" ),
            );

        $this->tableName = "dcd_message_loc";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("message_id", "build_id");
        parent::__construct($connector, $initialiserArray);

    }

    function createIndexes()
    {
        $sql = "CREATE UNIQUE INDEX p_dcd_msg_loc_1 on $this->tableName ( message_id,build_id,display_time );"; $ret = $this->connector->executeSQL($sql);

        return $ret;
    }
}

<?php

/**
    TempBuildAddress

    Holds for each bus stop build the last ip address
*/
class TempBuildAddress extends DataModel
{
    public $lastRefresh = false;
    public $refreshInterval = 3600;

    function __construct($connector = false)
    {
        $this->columns = array (
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer" ),
            "ip_address" => new DataModelColumn ( $this->connector,  "ip_address", "char", 20 ),
            "connect_date" => new DataModelColumn ( $this->connector,  "connect_date", "datetime" ),
            );
        $this->tableName = "t_build_address";
        $this->tempTable = true;
        $this->keyColumns = array("location_id");
        
        parent::__construct($connector);
    }

    function createPostIndexes()
    {   
        $sql = "CREATE INDEX i_t_build_addr ON t_build_address(build_id)";
        $ret = $this->connector->executeSQL($sql);

        return $ret;
    }

    function buildTable ()
    {
        // Only build if required ( not built yet or due for build )
        $now = new DateTime();
        if ( $this->lastRefresh && $this->lastRefresh->getTimestamp() > $now->getTimestamp() - $this->refreshInterval )
            return;

        $this->lastRrefesh = $now;

        echo "Building Temp Build Address\n";
        $this->dropTable();
        $this->createTable();

        $sql = 
		"INSERT INTO t_build_address
            SELECT UNIQUE b.build_id, ip_address, connect_date
             FROM display_point a, unit_build b, location c, outer gprs_mapping d
             WHERE a.build_id = b.build_id
             AND a.location_id = c.location_id
             AND d.build_id = b.build_id ";

        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to populate  t_build_address\n";
            return false;
        }

        if ( !$this->createPostindexes() )
            return false;

        return true;
    }

}

?>

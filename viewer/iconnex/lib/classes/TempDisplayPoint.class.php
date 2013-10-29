<?php

/**
    TempDisplayPoint

    Holds list of stops that the prediction distributor might like to 
    to message. This includes stands against bus stations using the multistop 
    station display mechanism
*/
class TempDisplayPoint extends DataModel
{
    public $lastRefresh = false;
    public $refreshInterval = 3600;

    function __construct($connector = false)
    {
        $this->columns = array (
            "location_id" => new DataModelColumn ( $this->connector,  "location_id", "integer" ),
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer" ),
            "display_type" => new DataModelColumn ( $this->connector,  "display_type", "char", 1 ),
            "filename" => new DataModelColumn ( $this->connector,  "filename", "char", 15 ),
            );
        $this->tableName = "t_display_point";
        $this->tempTable = true;
        $this->dbspace = false;
        $this->keyColumns = array("location_id", "build_id");
        

        parent::__construct($connector);
    }

    function createPostIndexes()
    {   
        $sql = "CREATE INDEX i_t_display_point ON t_display_point ( build_id )";
        $ret = $this->connector->executeSQL($sql);

        return $ret;
    }

    function buildTable ()
    {
        // Only build if required ( not built yet or due for build )
        $now = new DateTime();
        if ( $this->lastRefresh && $this->lastRefresh->getTimestamp() > $now->getTimestamp() - $this->refreshInterval )
            return;
        $this->lastRefresh = $now;

        echo "Building Temp Display Point table\n";
        $this->dropTable();
        $this->createTable();

        $sql = 
            "INSERT INTO t_display_point
            SELECT location_id, build_id, display_type, filename
            FROM display_point";

        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to phase 1 populate  t_display_point\n";
            return false;
        }

        
        $sql = "
	        INSERT INTO t_display_point
            SELECT  sl.stand_id,
                    dp.build_id,
                    dp.display_type,
                    dp.filename
            FROM    display_point dp,
                    station_loc sl
            WHERE   dp.display_type = 'U'
            AND     dp.location_id = sl.station_id
            ";
        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to phase 2 populate  t_display_point\n";
            return false;
        }


        $sql = "CREATE INDEX i_t_display_point ON t_display_point(location_id)";
        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to phase 2 populate  t_display_point\n";
            return false;
        }

        $sql = "CREATE INDEX i2_t_display_point ON t_display_point(build_id)";
        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to phase 2 populate  t_display_point\n";
            return false;
        }

        return true;
    }

}

?>

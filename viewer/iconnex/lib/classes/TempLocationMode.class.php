<?php

/**
    TempLocationMode

    Holds for each bus stop the default arrival/departure mode
*/
class TempLocationMode extends DataModel
{

    public $lastRefresh = false;
    public $refreshInterval = 3600;

    function __construct($connector = false)
    {
        $this->columns = array (
            "location_id" => new DataModelColumn ( $this->connector,  "location_id", "integer" ),
            "location_code" => new DataModelColumn ( $this->connector,  "location_code", "char", 30 ),
            "display_mode" => new DataModelColumn ( $this->connector,  "display_mode", "char", 1 ),
            "bay_no" => new DataModelColumn ( $this->connector,  "bay_no", "char", 10 ),
            );
        $this->tableName = "t_location_mode";
        $this->tempTable = true;
        $this->keyColumns = array("location_id");
        

        parent::__construct($connector);
    }

    function createPostIndexes()
    {   
        $sql = "CREATE INDEX i_".$this->tableName." ON ".$this->tableName."(location_id)";
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
        echo "Building Temp Location Mode table\n";
        $this->dropTable();
        $this->createTable();

        $sql = 
		"INSERT INTO ".$this->tableName."
			SELECT display_point.location_id,
				location.location_code,
				MIN(display_mode) display_mode,
				bay_no
			FROM  display_point, location
			WHERE display_point.display_type IN (\"B\", \"M\", \"T\", \"U\", \"R\", \"S\")
			AND location.location_id = display_point.location_id
			GROUP BY 1, 2, 4";

        if ( !( $this->connector->executeSQL($sql)) )
        {
            echo "Failed to populate  t_location_mode\n";
            return false;
        }

        return true;
    }

}

?>

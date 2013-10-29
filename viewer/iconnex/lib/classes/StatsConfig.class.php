<?php
/**
 * StatsConfig
 *
 * Datamodel for table stats_config
 */

class StatsConfig extends DataModel
{
    public $rules = array();
    public $rawData = false;
    public $statistics = array();
    public $statsModel = array();
    public $rawModel = array();
    public $rawStatisticClass = false;
    public $statisticClass = false;

    function __construct($connector = false)
    {
        $this->columns = array (
            "config_id" => new DataModelColumn($this->connector, "config_id", "serial"),
            "metric_type" => new DataModelColumn($this->connector, "metric_type", "varchar", 45),
            "period_type" => new DataModelColumn($this->connector, "period_type", "varchar", 45),
            "stale_after" => new DataModelColumn($this->connector, "stale_after", "interval"),
            "period_from" => new DataModelColumn($this->connector, "period_from", "datetime"),
            "period_to" => new DataModelColumn($this->connector, "period_to", "datetime"),
            "maintain_raw" => new DataModelColumn($this->connector, "maintain_raw", "smallint"),
            );

        $this->tableName = "stats_config";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("config_id");
        parent::__construct($connector);
    }

    /*
    ** builds core statistics tables if they dont exist
    **
    */
    function createConfigAndRules()
    {   
        // Either create a new stats config set for the relevant metric and period or
        // if it doesnt exist, or load existing ones
        if ( !$this->load ( array("metric_type", "period_type") ) )
        {
            // Create basic stats table
            $this->config_id = 0;
            $this->add();
            foreach ( $this->rules as $v )
            {
                $v->rule_id = 0;
                $v->config_id = $this->config_id;
                $v->add();
                //$v->dump();
            }   
        }
        else
        {
            $rule = new StatsRule($this->connector);
            //$this->connector->debug = 1;
            $this->rules = $rule->selectAll(false, "config_id = ". $this->config_id);
            //foreach ( $this->rules as $rule )
            //{
                //$rule->dump();
            //}
        }

        // Create Raw and Stats Tables
        $this->statsModel = StatsTable::Factory($this->connector, false,  $this->metric_type, $this->period_type, true);

        // Create raw data table for storing the data behind the stats data .. if required
        if ( $this->maintain_raw )
            $this->rawModel = StatsTable::Factory($this->connector, true,  $this->metric_type, $this->period_type, true);
    }

    /**
    * Receive a statistic value and store it in in memory statistics array, 
    * in memory raw data array (if necessary), store it in the database 
    * stats table and the database raw table
    */
    function handleStatisticEvent($key, $event)
    {
        // Apply statistic to stats array and table
        if ( !isset($this->statistics[$key]) )
        {
            // create new key entry
            $this->statistics[$key] = array();
            $this->statistics[$key]["data"] = array();
            $this->statistics[$key]["stats"] = call_user_func(array($this->statisticClass, 'FactoryForEvent'), $event);
        }
        $this->statistics[$key]["stats"]->applyStatisticFromEvent($this, $event);

        // Apply statistic to raw data
        if ( $this->maintain_raw )
        {
            //$data = $this->rawStatisticClass::FactoryForEvent($event);
            $data = call_user_func(array($this->rawStatisticClass, 'FactoryForEvent'), $event);
            $data->applyStatisticFromEvent($this, $event, $storeToDB);
            $this->statistics[$key]["data"][] = $data;
        }
        $this->removeStaleData();
    }

    /**
    * Passes through raw data and removes any that are stale .ie. have
    * timestamp prior to stale_after indicator
    */
    function removeStaleData()
    {
        // Get stale cut off threshold = now - stale_after
        $now = new DateTime();
        $timestamp = $now->getTimestamp() - 10;
        $now->setTimestamp($timestamp);

        $ct = 0;
        foreach ( $this->statistics as $key => $datasets )
        {
            $delct = 0;
            foreach ( $datasets["data"] as $rawkey => $raw )
            {
                $rawtime = DateTime::CreateFromFormat("Y-m-d H:i:s", $raw->timestamp);
                $rawtime = $rawtime->getTimestamp();
                if ( $rawtime < $timestamp )
                {
                    $raw->unapplyStatistic($this, $key);
                    $this->statistics[$key]["data"][$rawkey] = false;
                    unset($this->statistics[$key]["data"][$rawkey]);
                }
            }
        }
    }

    /**
    * Clears out stats and database of stats
    */
    function initializeAllStatStores()
    {
        $this->initializeMemoryStats();
        $this->initializeDatabaseStats();
    }

    /**
    * Clears out stats in memory store
    */
    function initializeMemoryStats()
    {
        $this->statistics = array();
    }

    /**
    * Builds a statistics and raw data model and clears the relevant tables
    */
    function initializeDatabaseStats()
    {
        // First drop and recreate the stats tables
        $this->statsModel = StatsTable::Factory($this->connector, false,  $this->metric_type, $this->period_type, true, true);

        // And drop the raw dataset
        if ( $this->maintain_raw )
        {
            $this->rawModel = StatsTable::Factory($this->connector, true,  $this->metric_type, $this->period_type, true, true);
        }

    }

    /**
    * Store all in memory data to tables
    */
    function rebuildDatabaseFromDataset()
    {

        $this->initializeDatabaseStats();
        foreach ( $this->statistics as $k => $v )
        {
            foreach ( $v["data"] as $key => $stat )
            {
                $stat->applyToDatabase($this);
            }
            $v["stats"]->saveStatisticModel($this, $this->statsModel);
        }
    }

    /**
    * Load the configs raw and statistics data set from the database
    */
    function buildDatasetFromDatabase()
    {

        $ar = $this->rawModel->sqlToInstanceArray("SELECT * FROM ".$this->rawModel->tableName);
        foreach ( $ar as $v )
        {
            $v->applyToInMemoryStatisticConfigs($this);
        }
        foreach ( $this->statistics as $k => $v )
        {
            $v["stats"]->saveStatisticModel($this, $this->statsModel);
        }
    }
}
?>

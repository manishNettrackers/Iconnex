<?php

/**
** Class: StatisticsGenerator
** ------------------------------
**
** Base statistics generator class for reading statistics generation rules
** from the database and populating both database tables named stats_metric_period
** and in memory store
**
*/

class StatisticsGenerator
{
    public $statisticType = "UNKNOWN";
    public $connector = false;
    public $configs = array();
    public $rawData = array();

    /*
    * Array of statistics configs to be covered by the generator
    * and all the period rules
    */
    public $configRules = array();

    // Create instance of Statistics Generator
    function __construct($connector = false)
    {
        if ($connector)
            $this->connector = $connector;
    }

    /*
    ** caclulate
    **
    */
    function calculate()
    {
    }

    /*
    ** builds core statistics tables if they dont exist
    **
    */
    function build()
    {
        // Create basic stats rules
        $table = new StatsConfig($this->connector);
        if ( !$table->tableExists())
        {
            $table->createTable();
            $table->config_id = 0;
            $table = new StatsRule($this->connector);
            $table->createTable();
            $table->config_id = 0;
        }

        // Create the configurations
        foreach ( $this->configRules as $k => $rule )
        {
            $rule->createStatsConfig();
        }
    }

    /**
    * Load up for each config a statistic set and raw data set
    */
    function buildDatasetFromDatabase()
    {
        foreach ( $this->configs as $config )
        {
            $config->buildDatasetFromDatabase();
        }
    }

    /**
    * Empties all statistics memory and data stores
    */
    function initializeDataStorage()
    {
        foreach ( $this->configs as $config )
        {
            $config->initializeAllStatStores();
        }
    }

    /**
    * Load up for each config a statistic set and raw data set
    */
    function rebuildDatabaseFromDataset()
    {
        foreach ( $this->configs as $config )
        {
            $config->rebuildDatabaseFromDataset();
        }
    }

    /**
    * Show the statistics of all the configs
    */
    function show($show_stat = true, $show_raw = false)
    {
        echo "\n=========================== \n";
        echo "Show \n";
        echo "=========================== \n";

        foreach ( $this->configs as $config )
        {
            echo "Statistics\n";
            echo "----------\n";
            foreach ( $config->statistics as $stats )
            {
                $stats["stats"]->show();
                if ( $show_raw )
                {
                    echo "Raw Data\n";
                    echo "--------\n";
                    foreach ( $stats["data"] as $raw )
                        $raw->show();
                }
            }
            //var_dump($config->statistics);
            //var_dump($config->rawData);
        }
    }
}
?>

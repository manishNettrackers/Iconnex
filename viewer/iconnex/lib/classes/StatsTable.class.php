<?php
/**
 * StatsTravelAverageXXX
 *
 * Datamodel for table stats_travel_avg
 */

class StatsTable extends DataModel
{
    function __construct($connector = false)
    {
        parent::__construct($connector);
    }

    /**
    * Produces a Data Model for a given stats type and period
    *
    */
    static function Factory($connector, $raw, $metric, $period, $create = false, $drop = false)
    {
        $class = "Stats${metric}XXX";
        if ( $raw )
            $class = "StatsRaw${metric}XXX";
        $object = new $class($connector);
        $object->tableName = preg_replace("/XXXX/", $period, $object->tableName);

        if ( $drop && $object->tableExists() )
        {
            $object->dropTable();
        }

        if ( $create && !$object->tableExists() )
            $object->createTable();

        return $object;
    }
}
?>

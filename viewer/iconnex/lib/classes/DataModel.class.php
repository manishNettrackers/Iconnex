<?php

class DataModel
{
    public $columns = false;
    public $dbspace = false;
    public $className = false;
    public $tableName = false;
    public $connector = false;
    public $keyColumns = false;
    public $tempTable = false;
    public $loaded = false;
    public $preparedInsert = false;
    public $preparedInsertCount = 0;    // Monitors number of inserts on a prepared insert
    public $preparedInsertLimit = 100;    // Identifies how many inserts allowed before reinitializing
                                        // prepared statement.

    // Create instace of Data Model, ans set connector
    //
    // connector - database connection to associate DataModel Instance with
    // initialiserArray - if set will create properties from all row elements
    // with matching key
    function __construct($connector = false, $initialiserArray = false)
    {
//echo " dm0 ".Utility::memory_increase();
        if ($connector)
            $this->connector = $connector;

//echo " dm1 ".Utility::memory_increase();

        // Automatically set class name property from $this
        if (!$this->className)
            $this->className = get_class($this);

        // Automatically create property for each data model column if not set
        foreach ($this->columns as $k => $v)
        {
            if (!isset ($this->$k))
                $this->$k = false;
        }

//echo " dm2 ".Utility::memory_increase();
        if ( $initialiserArray )
        {
            $this->initialiseFromArray($initialiserArray);
        }
//echo " dm3 ".Utility::memory_increase();
    }

    /*
    ** Convert array keyed with data model properties to instance properties/*
    */
    function initialiseFromArray($init)
    {
        foreach ( $this->columns as $k => $v )
        {   
            if ( isset ( $init[$k] ) )
            {
                $this->$k = trim($init[$k]);
            }
        }
    }

    function isKey($value)
    {
        foreach ( $this->keyColumns as $v )
        {   
            if ( $v == $value )
                return true;
        }
        return false;
    }

    function differs($compareWith, $include = false, $exclude = false)
    {
        $differs = false;
        foreach ( $this->columns as $k => $v )
        {   
            if ( $exclude && in_array ( $k, $exclude ) )
                continue;

            if ( $include && !in_array ( $k, $include ) )
                continue;

            if ( $this->$k != $compareWith->$k )
            {
                echo "$k dif ".$this->$k." != ".$compareWith->$k."\n";
                $differs = true;
                break;
            }
                
        }
        return $differs;
    }

    function tableExists()
    {
        if ( $this->connector->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "informix" )
        {
            if ( $this->connector->fetch1ValueSQL( "SELECT count(*) from systables where tabname = '".$this->tableName."'" ) > 0 )
            {
                return true;
            }
            else
            {
                 return false;
            }
        }
        else       
        {
            if ( $this->connector->fetch1ValueSQL( "SELECT count(*) from information_schema.tables where table_name = '".$this->tableName."'" ) > 0 )
                return true;
            else
                return false;
        }
    }

    function createTable()
    {
        if ( $this->tempTable )
            $sql = "CREATE ".$this->connector->syntax_temporary()." TABLE $this->tableName (";
        else
            $sql = "CREATE TABLE $this->tableName (";
        $sql .= $this->columnSyntaxList();
        $sql .= ")";

        if ( $this->dbspace )
            $sql .= $this->connector->syntax_in_dbspace($this->dbspace);

        if ( $this->tempTable )
            $sql .= $this->connector->syntax_with_no_log();

        $stat =  $this->connector->executeSQL($sql);
        if ( $stat )
        {
            $stat = $this->createIndexes();
        }

        return ( $stat );
    }

    function dropTable($continueOnError = true)
    {
        $sql = "DROP TABLE $this->tableName;";
        if ( $continueOnError )
            $ret = $this->connector->executeSQL($sql, "CONTINUE");
        else
            $ret = $this->connector->executeSQL($sql);

        return $ret;
    }

    function createIndexes()
    {
        return true;;
    }

    /* 
    */
    function selectAll($orderBy = false, $where = false, $classFromColumn = false)
    {

        $sql = "SELECT ";
        $sql .= $this->columnList(false, false, true);
        $sql .= " FROM ". $this->tableName;
        if ( $where )
            $sql .= " WHERE ".$where;
        if ( $orderBy )
            $sql .= " ORDER BY $orderBy";

        $stmt = $this->connector->executeSQL($sql);
        $returnArray = array();
        while ( ( $row = $stmt->fetch() ) )
        {
            if ( !$classFromColumn )
                $instance = new $this->className();
            else    
            {
                $class = $row[$classFromColumn];
                $instance = new $class();
                $instance->connector = $this->connector;
            }

            foreach ( $row as $k => $v )
            {
                if ( is_numeric($k) )
                    continue;
                $attr = $this->getAttributeForColumn($k);
                $instance->$attr = trim($v);
                $instance->columns["$attr"]->connector = $this->connector;
            }

            $returnArray[] = $instance;
        }
        $stmt->closeCursor();
        return ($returnArray);
    }

    /* 
    ** Takes an sql with columns myching the class definition and returns an array of class items
    */
    function sqlToSingleRowInstance($sql)
    {
        $stmt = $this->connector->executeSQL($sql);
        $returnArray = array();
        while ( ( $row = $stmt->fetch() ) )
        {
            $instance = new $this->className();
            $instance->connector = $this->connector;
            foreach ( $row as $k => $v )
            {
                if ( is_numeric($k) )
                    continue;
                $attr = $this->getAttributeForColumn($k);
                $instance->$attr = trim($v);
                $col = $instance->columns["$attr"];
                $col->connector = $this->connector;
            }
            return $instance;
        }
        $stmt->closeCursor();
        return false;
    }

    /**
     * Takes an sql with columns myching the class definition and returns an
     * array of class items
     */
    function sqlToInstanceArray($sql, $litecolumn = false)
    {
        //echo "\nTO ARAAYT $sql $litecolumn\n========\n";
            //echo "st loop ".Utility::memory_increase()."      -   ";

        $stmt = $this->connector->executeSQL($sql);
        $returnArray = array();
        $mem = memory_get_usage();

        //echo "init1 ".Utility::memory_increase()."\n";

        while (($row = $stmt->fetch()))
        {
            $popinstance = false;
            $instance = new $this->className();
            $litename = $this->className."_Lite";

            if ($litecolumn)
                $popinstance = new $litename();
//echo "start loop".Utility::memory_increase()."\n";
            /*
            $popinstance = false;
            $instance = new $this->className();
            //echo "init1 ".Utility::memory_increase()."\n";
            $litename = $this->className."_Lite";
            if ( $litecolumn )
                $popinstance = new $litename();
                */
            //echo "init2 ".Utility::memory_increase()."\n";
                
            $instance->connector = $this->connector;
            foreach ($row as $k => $v)
            {
                if (is_numeric($k))
                    continue;
                $attr = $this->getAttributeForColumn($k);
                if ($litecolumn)
                {
                    $popinstance->$attr = trim($v);
                    //echo "adlit $attr".Utility::memory_increase()."\n";
                }
                else 
                {
                    $instance->$attr = trim($v);
                    $col = $instance->columns["$attr"];
                    $col->connector = $this->connector;
                }
            }

            if ($litecolumn)
                $returnArray[] = $popinstance;
            else
                $returnArray[] = $instance;

            unset($popinstance);
            unset($instance);
        }
        unset($popinstance);
        unset($instance);
        unset($row);
        $stmt->closeCursor();
        unset ($stmt);

        $mem2 = memory_get_usage();
        $mem3 = $mem2 - $mem;
//echo "end loop ".count($returnArray)." ".Utility::memory_increase()." $mem - $mem2 = $mem3\n";

        return ($returnArray);
    }


    function columnList ($withoutKey = false, $keys = false, $forselect = false )
    {
        $sql = "";
        $ct = 0;
        foreach ( $this->columns as $k => $v )
        {
            if ( $withoutKey )
            {
                if ( $keys && in_array ( $k, $keys ) )
                    continue;
                else if ( !$keys && $this->isKey($v->name) )
                    continue;
            }
            if ( $ct++ > 0 )
                $sql .= ",";
            $sql .= $v->name;

            if ( $v->type == "interval" && $forselect )
                $sql .= " || '' $v->name";
        }
        return $sql;
    }

    function columnSyntaxList ()
    {
        $sql = "";
        $ct = 0;
        foreach ( $this->columns as $v )
        {
            $v->connector = $this->connector;
            if ( $ct++ > 0 )
                $sql .= ",";
            $sql .= $v->columnSyntax();
        }
        return $sql;
    }

    function columnValuePlaceHolders ($withoutKey = false, $keys = false)
    {
        $sql = "";
        $ct = 0;
        foreach ( $this->columns as $k => $v )
        {
            if ( $withoutKey )
            {
                if ( $keys && in_array ( $k, $keys ) )
                    continue;
                else if ( !$keys && $this->isKey($v->name) )
                    continue;
            }

            $name = $this->getAttributeForColumn($v->name);

            if ( $ct++ > 0 )
                $sql .= ",";
            $sql .= ":".$v->name;
        }
        return $sql;
            
    }

    function columnValueList($withoutKey = false, $keys = false)
    {
        $sql = "";
        $ct = 0;
        foreach ( $this->columns as $k => $v )
        {
            if ( $withoutKey )
            {
                if ( $keys && in_array ( $k, $keys ) )
                    continue;
                else if ( !$keys && $this->isKey($v->name) )
                    continue;
            }


            $name = $this->getAttributeForColumn($v->name);

            $value = $v->columnValue($this->$name);
            if ( $ct++ > 0 )
                $sql .= ",";
            $sql .= $value;
        }
        return $sql;
            
    }

    function addBindPrepareParameters()
    {
        if ( !$this->preparedInsert )
        {
            echo "adit";
            $this->addPrepare();
            if ( !$this->preparedInsert )
            {
                return false;
            }
        }

        foreach ( $this->columns as $k => $v )
        {
            $name = $this->getAttributeForColumn($v->name);
            $value = $v->columnValue($this->$name);
            $v->bindPrepareParameter($this->preparedInsert, $this->$name);
        }
    }

    function getAttributeForColumn ($columnName)
    {
        foreach ( $this->columns as $k => $v )
        {
            if ( $columnName == $v->name)
                return $k;
        }

        return $columnName;
            
    }

    function dump ()
    {
        echo "=== $this->className ========\n";
        foreach ( $this->columns as $k => $v )
        {
            $name = $this->getAttributeForColumn($v->name);
            echo " $k: ".$this->$name."\n";
        }
        echo "=====================================\n";
    }

    function add($getLastInsertId = false)
    {
        $stat = false;

        if ( $this->preparedInsert )
        {
             $this->addBindPrepareParameters();

             $stat = false;
             $stat = $this->preparedInsert->execute();

             if ( $stat && $getLastInsertId )
                $stat = $this->connector->lastInsertId($this->tableName);

             if ( $this->preparedInsertCount++ > $this->preparedInsertLimit )
             {
                $this->preparedInsert = false;
                $this->preparedInsertCount = 0;
                $this->addPrepare();
             }
        }
        else
        {
            $sql = "INSERT INTO ". $this->tableName. "(";
            $sql .= $this->columnList();
            $sql .= ") VALUES (";
            $sql .= $this->columnValueList();
            $sql .= ")";

            $stat =  $this->connector->executeSQL($sql);
        }

        if ($stat && count($this->keyColumns) == 1)
        {
            $attr = $this->getAttributeForColumn($this->keyColumns[0]);
            if ($this->columns["$attr"]->type == "serial")
            {
                $lastId = $this->connector->lastInsertId($this->tableName);
                $this->$attr = $lastId;
                if ($getLastInsertId)
                    return $lastId;
            }
        }

        return $stat;
    }

    function addPrepare()
    {
        $sql = "INSERT INTO ". $this->tableName. "(";
        $sql .= $this->columnList();
        $sql .= ") VALUES (";
        $sql .= $this->columnValuePlaceHolders();
        $sql .= ")";

        $this->preparedInsert =  $this->connector->prepareSQL($sql);
    }

    /**
     * @brief update the database record for the keys which should be set
     */
    function save($keys = false)
    {
        $sql = "UPDATE ". $this->tableName. " SET (";
        $sql .= $this->columnList(true, $keys);
        $sql .= ") = (";
        $sql .= $this->columnValueList(true, $keys);
        $sql .= ") WHERE 1 = 1";
        $sql .= $this->getKeysAsWhere($keys);
        $stat =  $this->connector->executeSQL($sql);

        return $stat;
    }

    function getKeysAsWhere($keys = false)
    {
        $sql = "";
        if ($keys)
        {
            foreach ($keys as $key)
            {
                foreach ($this->columns as $v)
                {
                    if ($v->name == $key)
                    {
                        $attr = $this->getAttributeForColumn($v->name);
                        $value = $this->columns[$attr]->columnValue($this->$attr);
                        if (strlen($this->$attr) == 0)
                            $sql .= " AND ".$v->name." IS NULL";
                        else
                            $sql .= " AND ".$v->name." = ".$value;
                    }
                }
            }
        }
        else
        {
            foreach ( $this->keyColumns as $v )
            {
                $attr = $this->getAttributeForColumn ($v);
                $value = $this->columns[$attr]->columnValue($this->$attr);
                if (strlen($this->$attr) == 0)
                    $sql .= " AND ".$v." IS NULL";
                else
                    $sql .= " AND ".$v." = ".$value;
            }
        }

        return $sql;
    }

    function delete($keys = false)
    {
        $this->loaded = false;

        $sql = "DELETE ";
        $sql .= " FROM ". $this->tableName;
        $sql .= " WHERE 1 = 1";
        $sql .= $this->getKeysAsWhere($keys);
        $stmt = $this->connector->executeSQL($sql);
        if (!$stmt)
            return false;
    }

    function count($keys = false, $where = false)
    {
        $this->loaded = false;

        $sql = "SELECT COUNT(*) select_count";
        $sql .= " FROM ". $this->tableName;
        $sql .= " WHERE 1 = 1";
        $sql .= $this->getKeysAsWhere($keys);
        if ( $where )
            $sql .= $where;
        $stmt = $this->connector->executeSQL($sql);
        if (!$stmt)
            return false;
        $ret = false;

        $this->selectCount = 0;
        while (($row = $stmt->fetch()))
        {
            $this->selectCount = $row["select_count"];
            if ( $this->connector->debug )
            {
                echo "Got Count $this->selectCount\n";
            }
            break;
        }
        $stmt->closeCursor();

        return  $this->selectCount;
    }

    function load($keys = false, $where = false)
    {
        $this->loaded = false;

        $sql = "SELECT ";
        if ( !$keys )
            $sql .= $this->columnList(true, false, true);
        else
            $sql .= $this->columnList(false, false, true);
        $sql .= " FROM ". $this->tableName;
        $sql .= " WHERE 1 = 1";
        $sql .= $this->getKeysAsWhere($keys);
        if ( $where )
            $sql .= $where;
        $stmt = $this->connector->executeSQL($sql);
        if (!$stmt)
            return false;
        $ret = false;
        while (($row = $stmt->fetch()))
        {
            foreach ( $row as $k => $v )
            {
                if ( is_numeric($k) )
                    continue;
                $attr = $this->getAttributeForColumn($k);
                $this->$attr = trim($v);
            }

            $this->loaded = true;
            break;
        }
        $stmt->closeCursor();

        return $this->loaded;
    }

    function rowFactory($connector, $class, $keys)
    {
        $obj = new $class($connector);
        $modelKeys = array();
        foreach ( $keys as $k => $v )
        {
            $obj->$k = $v;
            $modelKeys[] = $k;
        }
        return $obj->load($modelKeys);
    }

    function rowValueFactory($connector, $class, $keys, $column)
    {
        $obj = DataModel::rowFactory($connector, $class, $keys);
        if ( $obj )
            return $obj->$column;
    }
}

?>

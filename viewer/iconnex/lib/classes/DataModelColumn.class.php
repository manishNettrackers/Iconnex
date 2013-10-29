<?php

class DataModelColumn
{
    public $name = "";
    public $type = "";
    public $size = 0;
    public $allowNulls = true;
    public $value = false;
    public $connector = false;

    function __construct($connector, $name, $type, $size = 0, $allowNulls = true)
    {
//echo " dmc0 ".Utility::memory_increase().""; 
        $this->connector = $connector;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->allowNulls = &$allowNulls;
//echo " dmc1 ".Utility::memory_increase()."\n"; 
    }

    function columnValue($inNativeValue)
    {
        if ( strlen($inNativeValue) == 0)
            return 'NULL';

        $outNativeValue = $inNativeValue;
        switch ( $this->type )
        {
            case "char":
            case "varchar":
                if ( $this->connector )
                    return "'".$this->connector->sqlslashes($outNativeValue)."'";
                else
                    return "'".$outNativeValue."'";
                break;

            case "date":
            case "datetime":
            case "interval":
            case "datetimehourtosecond":
            case "datetimehourtoseconds":
                return "'".$outNativeValue."'";
                break;

            default:
                if ( $inNativeValue == 0 )
                    $outNativeValue = '0';
        }
        return $outNativeValue;
    }

    function bindPrepareParameter($stmt, $value)
    {
        switch ( $this->type )
        {
            case "serial":
            case "integer":
            case "smallint":
                $stmt->bindValue(":$this->name", $value, PDO::PARAM_INT);
                break;

            case "char":
            case "varchar":
            case "date":
            case "interval":
            case "datetimehourtosecond":
            case "datetimehourtoseconds":
            case "datetime":
                $stmt->bindValue(":$this->name", $value);
                break;

            default:
                echo "bindPrepareParameter Unknown type <".$this->type.">\n";
                return false;
                break;

        }
    }

    function columnSyntax()
    {
        switch ( $this->type )
        {
            case "serial":
                $ret = $this->name." ".$this->connector->syntax_create_serial();
                break;

            case "char":
                $ret = $this->name." char(".$this->size.")";
                break;

            case "varchar":
                $ret = $this->name." varchar(".$this->size.")";
                break;

            case "date":
                $ret = $this->name." date";
                break;

            case "interval":
                $ret = $this->name." ".$this->connector->syntax_time_interval_column();
                break;

            case "datetimehourtosecond":
            case "datetimehourtoseconds":
                $ret = $this->name." ".$this->connector->syntax_datetime_hour_to_second_column();
                break;

            case "datetime":
                $ret = $this->name." ".$this->connector->syntax_datetime_column();
                break;

            case "smallint":
                $ret = $this->name." INTEGER";
                break;

            case "integer":
                $ret = $this->name." INTEGER";
                break;

            case "decimal":
                if ( $this->size )
                    $ret = $this->name." DECIMAL(".$this->size.")";
                else
                    $ret = $this->name." DECIMAL(16)";
                break;

            case "serial":
                $ret = $this->name." ".$this->connector->syntax_serial();
                break;

            default:
                echo "Unknown type <".$this->type.">\n";
                $ret =false;
                break;


        }
        return $ret;
        if ( $ret )
        {
            if ( !$this->allowNulls )
                $ret .= " NOT NULL";
            return $ret;
        }

        return false;
    }
}

?>

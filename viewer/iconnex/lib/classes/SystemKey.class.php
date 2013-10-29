<?php

require_once("DataModel.class.php");

class SystemKey extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "key_code" => new DataModelColumn ( $this->connector,  "key_code", "char", 10 ),
            "key_value" => new DataModelColumn ( $this->connector,  "key_value", "char", 60 ),
            );
        $this->tableName = "system_key";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("key_code");
        
        parent::__construct($connector, $initialiserArray);
    }

    /*
    ** getOutboundQueue
    **
    ** Returns outbound queue for writing messages out
    */
    static function getOutboundQueue($connector)
    {
        $syskey = new self($connector);
        $syskey->key_code = "AROBQ";
        if ( $syskey->load() )
        {
            return msg_get_queue($syskey->key_value);
        }
        return false;
    }

    /*
    ** getInboundQueue
    **
    ** Returns inbound quete for an Event Handler
    */
    static function getInboundQueue($connector, $queueName = "ARIBQ", $defaultId = false )
    {
        $syskey = new self($connector);
        $syskey->key_code = $queueName;
        if ( $syskey->load() )
        {
            return msg_get_queue($syskey->key_value);
        }
        else if ( $defaultId )
        {
            return msg_get_queue($defaultId);
        }

        return false;
    }

    /*
    ** getTaskQueue
    **
    ** Returns inbound quete for an Event Handler
    */
    static function getTaskQueue($connector, $queueName = "ARIBQ", $defaultId = false )
    {
        $syskey = new self($connector);
        $syskey->key_code = $queueName;
        if ( $syskey->load() )
        {
            return msg_get_queue($syskey->key_value);
        }
        else if ( $defaultId )
        {
            return msg_get_queue($defaultId);
        }

        return false;
    }

    /*
    ** getUDPListenerPort
    **
    ** Returns the UDP port that the UDP listener is 
    ** connected to
    */
    static function getUDPListenerPort($connector)
    {
        $syskey = new self($connector);
        $syskey->key_code = "UDPPORT";
        if ( $syskey->load() )
        {
            return $syskey->key_value;
        }
        return false;
    }

    /*
    ** getKeyValue
    **
    ** Returns a system Key Value
    */
    static function getKeyValue($connector, $code)
    {
        $syskey = new self($connector);
        $syskey->key_code = $code;
        if ( !$syskey->load() )
        {
            return false;
        }
        return $syskey->key_value;
    }

}

?>

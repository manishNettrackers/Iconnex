<?php
/**
* ImportOperatorMap
*
* Datamodel for table event_type
*
*/

class EventType extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "event_type" => new DataModelColumn ( $this->connector, "event_type", "integer" ),
            "event_class" => new DataModelColumn ( $this->connector, "event_class", "varchar", 30 ),
            "description" => new DataModelColumn ( $this->connector, "description", "varchar", 30 ),
            );

        $this->tableName = "event_type";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("event_type");
        parent::__construct($connector);
    }

    function createTable()
    {
        parent::createTable();
        $this->createEventType(244, "journey_details", "ETM Journey Details");
        $this->createEventType(703, "journey_details", "DAIP ETM Journey Details");
        $this->createEventType(700, "basic", "DAIP Log On");
        $this->createEventType(701, "basic", "DAIP Log Off");
        $this->createEventType(704, "position_update", "DAIP Position Update");
        $this->createEventType(121, "position_update", "Position Update");
    }

    function createEventType($type, $class, $desc)
    {
        $obj = new self($this->connector);
        $obj->event_type = $type;
        $obj->event_class = $class;
        $obj->description = $desc;
        if ( !$obj->load() )
            $obj->add();
        else
            $obj->save();
    }
}


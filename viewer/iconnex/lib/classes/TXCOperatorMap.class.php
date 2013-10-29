<?php
/**
* ImportOperatorMap
*
* Datamodel for table serviced_organisation
*
*/

class TXCOperatorMap extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "txc_operator" => new DataModelColumn ( $this->connector, "txc_operator", "char", 30 ),
            "national_operator" => new DataModelColumn ( $this->connector, "national_operator", "char", 30 ),
            );

        $this->tableName = "txc_operator_map";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("txc_operator");
        parent::__construct($connector);
    }

    function createTable()
    {
echo "Create";
        parent::createTable();
        $this->createMapping("5370", "5370");
        $this->createMapping("214", "ASES");
        $this->createMapping("153", "GPLM");
        $this->createMapping("5058", "REDL");
        $this->createMapping("271", "RRTR");
        $this->createMapping("5100", "SCBD");
        $this->createMapping("ST", "SCNH");
        $this->createMapping("5288", "SOUL");
        $this->createMapping("5504", "VLET");
        $this->createMapping("391", "ZSIN");
    }

    function createMapping($txc, $national)
    {
        $obj = new self($this->connector);
        $obj->txc_operator = $txc;
        $obj->national_operator = $national;
        if ( !$obj->load() )
            $obj->add();
        else
            $obj->save();
    }
}


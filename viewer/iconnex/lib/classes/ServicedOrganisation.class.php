<?php
/**
* ServicedOrganisation
*
* Datamodel for table serviced_organisation
*
*/

class ServicedOrganisation extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "org_id" => new DataModelColumn ( $this->connector, "org_id", "serial" ),
            "org_code" => new DataModelColumn ( $this->connector, "org_code", "char", 30 ),
            "org_name" => new DataModelColumn ( $this->connector, "org_name", "char", 50 ),
            );

        $this->tableName = "serviced_organisation";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("org_id");
        parent::__construct($connector);

    }
}
?>

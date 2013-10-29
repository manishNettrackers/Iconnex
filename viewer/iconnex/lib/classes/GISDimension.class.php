<?php
/**
* GISDimension
*
* Datamodel for table gis_dimension
*
*/

class GISDimension extends DataModel
{
    function __construct($connector = false)
    {
        $this->columns = array (
            "gis_id" => new DataModelColumn($this->connector, "gis_id", "serial"),
            "geohash" => new DataModelColumn($this->connector, "geohash", "char", "20"),
            "geohash2" => new DataModelColumn($this->connector, "geohash2", "char", "20" ),
            "osm_place_id" => new DataModelColumn($this->connector, "osm_place_id", "integer"),
            "latitude" => new DataModelColumn($this->connector, "latitude", "decimal(12,5)"),
            "longitude" => new DataModelColumn($this->connector, "longitude", "decimal(12,5)"),
            "addr_road" => new DataModelColumn($this->connector, "addr_road", "varchar", "50"),
            "addr_suburb" => new DataModelColumn($this->connector, "addr_suburb", "varchar", "50"),
            "addr_city" => new DataModelColumn($this->connector, "addr_city", "varchar", "50"),
            "addr_country" => new DataModelColumn($this->connector, "addr_country", "varchar", "50"),
            "addr_county" => new DataModelColumn($this->connector, "addr_county", "varchar", "50"),
            "addr_postcode" => new DataModelColumn($this->connector, "addr_postcode", "varchar", "50")
            );

        $this->tableName = "gis_dimension";
        $this->dbspace = "centdbs";
        $this->keyColumns = array("gis_id");
        parent::__construct($connector);
    }
}
?>

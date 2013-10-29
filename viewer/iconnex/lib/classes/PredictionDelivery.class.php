<?php
/**
* PredictionDelivery
*
* Datamodel for table dcd_prediction
*
*/

class PredictionDelivery extends DataModel
{
    function __construct($connector = false, $initialiserArray = false)
    {
        $this->columns = array (
            "id" => new DataModelColumn ( $this->connector, "id", "serial" ),
            "journey_fact_id" => new DataModelColumn ( $this->connector, "journey_fact_id", "integer" ),
            "sequence" => new DataModelColumn ( $this->connector, "sequence", "integer" ),
            "send_time" => new DataModelColumn ( $this->connector, "send_time", "datetime" ),
            "pred_type" => new DataModelColumn ( $this->connector, "pred_type", "char", 1 ),
            "display_mode" => new DataModelColumn ( $this->connector, "display_mode", "char", 1 ),
            "rtpi_eta_sent" => new DataModelColumn ( $this->connector, "rtpi_eta_sent", "datetime" ),
            "rtpi_etd_sent" => new DataModelColumn ( $this->connector, "rtpi_etd_sent", "datetime" ),
            "pub_eta_sent" => new DataModelColumn ( $this->connector, "pub_eta_sent", "datetime" ),
            "pub_etd_sent" => new DataModelColumn ( $this->connector, "pub_etd_sent", "datetime" ),
            "prediction" => new DataModelColumn ( $this->connector, "prediction", "datetime" ),
            "bay_no" => new DataModelColumn ( $this->connector, "bay_no", "char", 10 ),
            );

        $this->tableName = "prediction_delivery";
        $this->dbspace = "centdbs";
        $this->keyColumns = array();
        parent::__construct($connector, $initialiserArray);

    }
}
?>

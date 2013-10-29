<?php

/**
** TempPredictionStopParam
**
** Creates delivery parameters for each build in the system.
** Specifies 
*
*/
class TempPredictionStopParam extends DataModel
{
    public $lastRefresh = false;
    public $refreshInterval = 3600;

    public $componentstmt = false;

    function __construct($connector)
    {
        $this->columns = array ( 
            "build_id" => new DataModelColumn ( $this->connector,  "build_id", "integer" ),
            "param_desc"  => new DataModelColumn ( $this->connector,  "param_desc", "char", 20 ),
            "param_value"  => new DataModelColumn ( $this->connector,  "param_value", "char", 30 ),
            );

        $this->tableName = "t_stop_param";
        $this->tempTable = true;
        //$this->className = "TempPredictionStopParam";
        $this->keyColumns = array ( "build_id", "param_desc" );

        parent::__construct($connector);
    }

    function createPostIndexes()
    {   
        $sql = "CREATE INDEX i_t_stop_param ON t_stop_param ( build_id )";
        $ret = $this->connector->executeSQL($sql);

        return $ret;
    }

    function buildTable ()
    {
        // Only build if required ( not built yet or due for build )
        $now = new DateTime();
        if ( $this->lastRefresh && $this->lastRefresh->getTimestamp() > $now->getTimestamp() - $this->refreshInterval )
            return;

        $this->lastRrefesh = $now;

        echo "Building Prediction Parameter Stop table\n";
        $this->dropTable();
        $this->createTable();

        $sql = 
            "SELECT UNIQUE b.build_id, unit_type, build_code
            FROM unit_build b
            JOIN t_display_point a ON a.build_id = b.build_id
            AND unit_type in ('UDPRTPI', 'TCPSERV', 'BUSSTOP', 'BUSMEDIA' )";

        if ( !( $stmt = $this->connector->executeSQL($sql)) )
        {
            echo "Failed to search display points\n";
            return false;
        }

        while ( $row = $stmt->fetch() )
        {
            $this->build_id = $row["build_id"];
            $unittype = trim($row["unit_type"]);

            switch ( $unittype )
            {
                case "UDPRTPI":
				    $this->param_value = $this->fetch_build_param ( $this->build_id, "UDPRTPI", "ipAddress" );
				    $this->param_desc = "ipAddress";
                    if ( $this->param_value )
                        $this->add();
                    
                    break;

			    case "TCPSERV":
				    $this->param_value = $this->fetch_build_param ( $this->build_id, "BTRTCONF", "gatewayId" );
				    $this->param_desc = "gatewayId";
                    if ( $this->param_value )
                        $this->add();
				
				    $this->param_value = $this->fetch_build_param ( $this->build_id, "SCOOTXML", "gatewayId" );
				    $this->param_desc = "gatewayId";;
                    if ( $this->param_value )
                        $this->add();
                    
				    $this->param_value = $this->fetch_build_param ( $this->build_id, "SCOOTXML", "vccCode" );
				    $this->param_desc = "vccCode";
                    if ( $this->param_value )
                        $this->add();

                    break;

			        case "BUSSTOP":
                    case "UDPRTPI":
                    case "TCPSERV":
                    case "BUSMEDIA":

				        $this->param_value = $this->fetch_build_param ( $this->build_id, "STOPDISPLAYDEVICE", "countdownMsgType" );
					    $this->param_desc = "countdownMsgType";
                        if ( $this->param_value )
                            $this->add();

				        $this->param_value = $this->fetch_build_param ( $this->build_id, "STOPDISPLAYDEVICE", "destinationType" );
					    $this->param_desc = "destinationType";
                        if ( $this->param_value )
                            $this->add();

				        $this->param_value = $this->fetch_build_param ( $this->build_id, "STOPDISPLAYDEVICE", "messageBundles" );
					    $this->param_desc = "messageBundles";
                        if ( $this->param_value )
                            $this->add();
            }
        }

        $this->createPostindexes();
    }

    function fetch_build_param($build_id, $component, $param)
    {

        if ( !$this->componentstmt )
        {
            $sql = 
		            " select unit_param.param_value, unit_build.build_code". 
                        " from unit_param, parameter, component, unit_build".
                        " where 1 = 1".
                        " and component.component_id = parameter.component_id".
                        " and unit_param.component_id = parameter.component_id".
                        " and unit_param.param_id = parameter.param_id".
                        " and unit_param.build_id = :build_id".
                        " and unit_param.build_id = unit_build.build_id".
			            " and component_code = :component".
			            " and param_desc = :param";

            if ( !$this->componentstmt = $this->connector->prepareSQL($sql) )
            {
                echo "Failed to preapare unit component select\n";
                return false;
            }
        }

        // Place holder for unit_build record
        $ub = new UnitBuild($this->connector);

        $paramval = false; 

        // First find any build
        while ( true )
        {
            $this->componentstmt->bindValue(":build_id", $build_id, PDO::PARAM_INT);
            $this->componentstmt->bindValue(":component", $component, PDO::PARAM_STR);
            $this->componentstmt->bindValue(":param", $param, PDO::PARAM_STR);

            if ( $stmt = $this->componentstmt->execute() )
            {
                if ( $row = $this->componentstmt->fetch() )
                {
                    $paramval = trim($row["param_value"]);
    
                    // We have a non null parameter value
                    if ( $paramval )
                        break;
                }
            }

            $build_parent = 0;

            $ub->build_id = $build_id;
            if ( !$ub->load() )
                break;

            if ( !$ub->build_parent )
                break;

            if ( $ub->build_parent == $build_id )
                break;

            $build_id = $ub->build_parent;
        }

        return $paramval;
    }

}

?>

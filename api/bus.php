<?php
/**
 * @brief Base gateway engine
 *  
 * Base class for handling gateway request
 *
 */
class open_transport_gateway
{
    var $api_engine = false;
    var $action = "unknown";
    var $output_format = "xml";

    function invoke_api_engine()
    {
        $this->api_engine = new reportico($this);
    }
}

/**
 * @brief Gateway engine handling Bus Requests
 *  
 * Base class for handling gateway request
 *
 */
class open_transport_gateway_bus extends open_transport_gateway
{
    function __construct()
    {
        // Load Reporting Engine
        $this->api_engine = new reportico($this);
    }

    function provide($action, $url, $output_format)
    {
        $this->action = $action;
        $this->output_format = $output_format;

        $this->invoke_api_engine();

        $_REQUEST["project"] = "apiv1";
        $_REQUEST["execute_mode"] = "EXECUTE";
//        $_REQUEST["clear_session"] = "1";
        $_REQUEST["target_format"] = strtoupper($this->output_format);

        header("Cache-Control: no-cache, must-revalidate");

        switch ($this->action)
        {
            case "services":
                $route_code = array_shift($url);
                if ($route_code)
                    $_REQUEST["MANUAL_route"] = $route_code;
                $this->services();
                break;

            case "locations":
                $location_code = array_shift($url);
                if ($location_code)
                    $_REQUEST["MANUAL_location"] = $location_code;

                $_REQUEST["MANUAL_route"] = get_request_param("service", false);
                $_REQUEST["user"] = "admin";
                $this->locations();
                break;

            case "servicepatterns":
                $route_code = get_request_param("service", false);
                $this->servicepatterns($route_code);
                break;

            case "calls":
                $location_code = array_shift($url);
                if ($location_code)
                {
                    $_REQUEST["locations"] = $location_code;
                    $this->calls();
                }
                else
                {
                    doc();
                    die;
                }

                break;

            case "status":
                $_REQUEST["MANUAL_route"] = get_request_param("service", false);
                $this->status();
                break;

            case "mybus":
                $function = array_shift($url);
                if ($function)
                {
                    switch ($function)
                    {
                        case "getTopoId":
                            $reply = '{"topoId":"cfc1f7faa5ba24483432febe16930ae1"}';
                            $len = strlen($reply);
                            header("Content-Length: $len");
                            header("Content-Type: application/json");
                            echo $reply;
                            die;

                        case "DatabaseVersion":
                            $timestamp = date("Y-m-d");
                            $reply = '{"timestamp":"' . $timestamp . '",'
                                . '"db_url":"http://ods.reading-travelinfo.co.uk/mybus/busstops-cfc1f7faa5ba24483432febe16930ae1-MBE_8.db",'
                                . '"db_schema_version":"MBE_8",'
                                . '"checksum":"' . md5_file("../mybus/busstops-cfc1f7faa5ba24483432febe16930ae1-MBE_8.db") . '",'
                                . '"topo_id":"cfc1f7faa5ba24483432febe16930ae1"'
                                . '}';
                            $len = strlen($reply);
                            header("Content-Length: $len");
                            header("Content-Type: application/json");
                            echo $reply;
                            die;

                            $len = strlen($reply);
                            header("Content-Length: $len");
                            header("Content-Type: application/json");
                            echo $reply;
                            die;

                        default:
                            echo "invalid";
                            die;
                    }
                }
                else
                {
                    doc();
                    die;
                }

            default:
                doc();
                die;
                break;
        }
    }

    function services()
    {
        $_REQUEST["xmlin"] = "services.xml";
        $this->api_engine->execute();
    }

    function locations()
    {
        $_REQUEST["xmlin"] = "locations.xml";
        $this->api_engine->execute();
    }

    function servicepatterns($route_code)
    {
        include_once "servicepatterns.php";
        service_patterns($route_code);
    }

    function calls()
    {
        set_include_path(get_include_path().":/opt/ods/web/viewer/iconnex/protected/config");
        set_include_path(get_include_path().":/opt/ods/web/viewer/iconnex/protected/views/webstop");
        include_once "libdb.php";
        include_once "webstop.php";
        webstop();
        if (strtoupper($this->output_format) == "JSON")
            webstop_display_json();
        else
            webstop_display_xml();
    }

    function status()
    {
        $_REQUEST["project"] = "apiv1_rti";
        $_REQUEST["xmlin"] = "status.xml";
        $this->api_engine->execute();
    }
}
?>

<?php
error_reporting(E_ALL);

include_once("bus.php");

global $versions;
global $g_data_format;

function doc()
{
    header($_SERVER["SERVER_PROTOCOL"]. " 200");
    header("Content-Type: text/html; charset=utf-8");
    include_once("doc.php");
}

function error_response($text)
{
    global $g_data_format;

    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

    $response = array("error" => $text);

    if ($g_data_format == "json")
    {
        echo json_encode($response);
    }
    else // if ( $g_data_format == "xml" )
    {
        $xml = new SimpleXMLElement('<ods/>');
        foreach ($response as $k => $v)
        {
            $xml->addChild($k, $v);
        }
        echo $xml->AsXml();
    }
}

/**
 * @brief Fetch a _GET or _POST variable into a veriable
 *  
 * Searches $_REQUEST for a parameter and if not found returns the default
 *
 * @param in_val The parameter name
 * @param in_default The default to use if not found
 *
 * @return the GET/POST  or default value
 */
function get_request_param($in_val, $in_default = false, $in_default_condition = true)
{
    if (array_key_exists($in_val, $_REQUEST))
        $ret = $_REQUEST[$in_val];
    else
        $ret = false;

    if ($in_default && $in_default_condition && !$ret)
        $ret = $in_default;

    return ($ret);
}

function api()
{
    global $versions;
    global $g_data_format;

    set_include_path(get_include_path().":/opt/ods/web/viewer/iconnex/protected/extensions/reportico");
    include_once('reportico.php');

    $url = explode('/', $_GET['url']);
    $version = array_shift($url);
    if (!$version)
    {
        doc();
        die;
    }

    $datasource = array_shift($url);
    if (!$datasource || !in_array($version, $versions))
    {
        doc();
        die;
    }

    $g_data_format = get_request_param("format", "xml");

    switch ($datasource)
    {
        case "bus":
            $gateway = new open_transport_gateway_bus();
            $action = array_shift($url);
            if (!$action)
            {
                doc();
                die;
            }
            
            $gateway->provide($action, $url, $g_data_format);
            break;

        case "parking":
            doc();
            die;
            break;

        default:
            doc();
            die;
    }
}

$versions = array(0 => 1);
api();
?>

<?php

/*
 * class restRequest
* provide a simple utilities to process a http request and handle its objects
 * 
 * @package ICONEX
 * @extension API
 * @author  Reda Benjli <redalisma@gmail.com>
 */


class requestHandler extends requestWrapper {
    
    
      public static function processRequest() {

        $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $objectToReturn = new requestWrapper();

        $data = array();

        switch ($requestMethod) {

            case 'get':
                $data = $_GET;
                break;

            case 'post':
                $data = $_POST;
                break;

            case 'put':
// basically, we read a string from PHP's special input location,
// and then parse it out into an array via parse_str... per the PHP docs:
// Parses str  as if it were the query string passed via a URL and sets
// variables in the current scope.
                parse_str(file_get_contents('php://input'), $put_vars);
                $data = $put_vars;
                break;
        }

// store the method
        $objectToReturn->setMethod($requestMethod);

// set the raw data, so we can access it if needed 
        $objectToReturn->setRequestParams($data);

        if (isset($data['data'])) {
// translate the JSON to an Object
            $objectToReturn->setData(json_decode($data['data']));
        }
        return $objectToReturn;
    }

   public static function sendResponse($response) {
 
       echo json_encode($response);
            exit;
    }

 /*   public static function getStatusCodeMessage($status) {
// these could be stored in a .ini file and loaded
// via parse_ini_file()... however, this will suffice
// for an example
        $responseCodes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($responseCodes[$status])) ? $responseCodes[$status] : '';
    }
    */
      public static function Log($msg) {

        error_log(print_r($msg, true));
        error_log("***********************");
    }
    
 }
?>

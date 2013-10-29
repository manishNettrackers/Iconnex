<?php

/*
 * class RestUtility
 * 
 * provide a simple utilities to process a http request and wrapp it to an object
 * 
 * @package ICONEX
 * @extension API
 * @author  Reda Benjli <redalisma@gmail.com>
 */

   class requestWrapper {

    private $request_params = "";
    private $data;
    private $http_acceptance = "";
    private $method = "";

    
    
    public function __construct() {

        //declare array
        $this->request_params = array();
        //init data 
        $this->data = "";
        //get the http header accept
        $this->http_acceptance = (strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
        //init the method
        $this->method = 'GET';
    }

    public function setData($data) {

        $this->data = $data;
    }

    public function setRequestParams($requestparams) {

        $this->request_params = $requestparams;
    }

    public function setMethod($method) {

        $this->method = $method;
    }

    public function getData() {

        return $this->data;
    }

    public function getRequestParams() {

        return $this->request_params;
    }

    public function getMethod() {

        return $this->method;
    }

    public function getHttpAcceptance() {

        return $this->http_acceptance;
    }

  
    

}

?>

<?php

require_once('webprovider.php');
/*
 * Tradehill API Library (Public, Trading, Withdrawal & Deposit)
 * 
 * @author    Brandon Beasley <http://brandonbeasley.com/>
 * @copyright Copyright (C) 2011 Brandon Beasley
 * @license   GNU GENERAL PUBLIC LICENSE (Version 3, 29 June 2007)
 * 
 *          Please consider donating if you use this library:
 *            
 *              1Eg9CYLdPeJ1YtYx9cSJmsdnvKBWVRMzpS
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

class nominatim extends webprovider{
    
    //Set to TRUE for API test environment
    //Set to FALSE for API production environment
    const API_TEST     = FALSE;
    
    //API Configuration
    const API_URL       = 'http://nominatim.openstreetmap.org/';
    const RESULT_FORMAT = 'array'; //default is 'json'
    
    private $publicFunctions  = array('reverse');
    private $latitude;
    private $longitude;
    
    public function __construct($latitude, $longitude){
	$this->longitude = $longitude;
	$this->latitude = $latitude;
    }
            
    public function __call($method, $params = array()) {
        
        $this->_validateMethod($method);        
        $url = $this->_buildUrl($method);

        if (in_array($method, $this->publicFunctions)){
            $options = $this->_buildParams($method , $params);
        } else {
            $options = NULL;
        }

        $result = $this->_connectGet($url, $options);
        $result = $this->_formatResults($result);
        
        return $result;
    }
    
    private function _buildParams($method, $params = array()){
        
        if (in_array($method, $this->publicFunctions)) {
            $params[0]['lat'] = $this->latitude;
            $params[0]['lon'] = $this->longitude;
            $params[0]['addressdetails'] = "1";
            $params[0]['format'] = "json";

            foreach ($params[0] as $k => $v) {
                $options[$k] = $v;
            }
        } else {
            $options = NULL;
        }
        
        return $options;
    }
    
        
    private function _buildUrl($method){
        
        $url = self::API_URL . $method ;
        
        return $url;
    }
            
    private function _validateMethod($method){
                           
        if(in_array($method, $this->publicFunctions) 
                OR in_array($method, $this->tradingFunctions) 
                      OR in_array($method, $this->wdFunctions)){
                        return TRUE; 
        } else {
            die('FAILURE: Unknown Method'); 
        }
    }
    
    private function _formatResults($results){
        
        if(self::RESULT_FORMAT == strtolower('array')){
        $results = json_decode($results, true);
        }
        
        return $results;
    }
        
    private function _connectGet($url, $params = NULL){
        
        //open connection

	$url .= "?".http_build_query($params);
        $ch = curl_init();
                        
        //set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_PROXY, "http://10.0.100.1:3128");
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);

        //execute CURL connection
        $returnData = curl_exec($ch);
                
        if( $returnData === false)
        {
            die('<br />Connection error:' . curl_error($ch));
        }
        //close CURL connection
        curl_close($ch);
                                
        return $returnData;
    }
    private function _connectPost($url, $params = NULL){
        
        //open connection
        $ch = curl_init();
                        
        //set the url
        curl_setopt($ch, CURLOPT_URL, $url);
                
        //add POST fields
        if ($params != NULL){
                        
            //url encode params array before POST
            $postData = http_build_query($params, '', '&');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        
        //MUST BE REMOVED BEFORE PRODUCTION (USE to bypass SSL Cert)
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; tradehill-php; '
            .php_uname('s').'; PHP/'.phpversion().')');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
               
        //execute CURL connection
        $returnData = curl_exec($ch);
                
        if( $returnData === false)
        {
            die('<br />Connection error:' . curl_error($ch));
        }
        
        //close CURL connection
        curl_close($ch);
                                
        return $returnData;
    }

}



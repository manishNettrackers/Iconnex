<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class criteriaBuilder {
 

    public function __construct() {
        
        self::$this;
    }

    public function __destruct() {
        ;
    }

    public static function returnhtml($criterias) {

        $f = new criteriaBuilder();
        // requestHandler::Log('***   criteria builder ****');

        foreach ($criterias as $singlecriteria) {

            if (!method_exists($f, $singlecriteria)) {

                throw new Exception('wrong criteria suplied');
            }

            $foo = $f->$singlecriteria();


            //        requestHandler::Log($foo);
            return $foo;
        }
    }

    /* generate the html and css layer.
     * 
     * @params : array ($criteria1, $criteria2, $criteria3 ....)
     * 
     * @return $html   html formatted code
     */

    public function drawLayer() {
        
    }

    public function daterange() {
        
    }

    public function timerange($param) {
        
    }

    public function operator() {
return '<select type = "text" i>';

    }

}

?>

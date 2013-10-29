<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Rest {

    private $_connexion = NULL;
    private $_sqlquery = NULL;
    private $_datareader = NULL;

    /* init the yii database layer
     * 
     */

    public function __construct() {

        try {

            $this->_connexion = Yii::app()->db;
            
            if (!($this->_connexion->active)) {
                throw new Exception("connexion init failed");
                $this->Log('connexion init failed');
            }
        }
        //check if the connexion is active
        catch (Exception $e) {
            Log($e->$msg);
            throw new Exception(" Database connexion problem");
        }
    }

    /* log the error into the log file
     * 
     */

    public function Log($msg) {

        error_log(print_r($msg, true));
        error_log("***********************");
    }

}

?>

<?php

/*
 * UnitTests for the api class
 * 
 * @authr reda benjli
 * @package iconnex
 */

class ApiTest extends CTestCase {

    // holds the db connec

    private $_dbconnexion ="";
    // holds the the redis connexion
    private $_rconnexion;

    public function getConnectionDb() {

        if (is_null($this->_dbconnexion)) {

            $this->_dbconnexion = yii::app()->db;
        }


        return $this->_dbconnexion;
    }

    public function createTempTable() {

        //create temp table
        $this->getConnectionDb()->createCommand('create temporary TABLE iF NOT EXISTS temp (var text(600)) ')
                ->execute();
    }

    /* create new instance of the redis server
     * 
     */

    public function getconnectionredis() {

        // if (is_null($_rconnexion)) {

        $this->_rconnexion = Yii::createComponent(
                        array(
                            "class" => 'ext.redis.ARedisConnection',
                            "hostname" => 'localhost',
                            "port" => 6378
                        )
        );

        return $this->_rconnexion;
        //   }
    }

    /* test the basic crud funccionality
     * 
     */

    public function testCrudOperations() {

        //raw data
        $rawdata = 'this is a new unit testing rawdata ';
        $rawdatamodified = 'this is a modified unit testing rawdata ';


        //create temp table
        $this->createTempTable();

        //insert raw data into temp table
        $this->getConnectionDb()->createCommand('insert into temp (var) values ("' . $rawdata . '")')
                ->execute();

        //get the inserted data
        $currentdata = $this->getConnectionDb()->createCommand()
                ->select('var')
                ->from('temp')
                ->queryColumn();

        //create temp teble
        $this->createTempTable();


        $this->getConnectionDb()->createCommand()
                ->update('temp', array('var' => $rawdatamodified))
                ->query();


        //assert insert
        $this->assertEquals($currentdata[0], $rawdata);

        //assert update
        $this->assertEquals($currentdata[0], $rawdatamodified);
    }

}

?>

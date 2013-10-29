<?php

class ActiveItem 
{
    public $unit_build;

    // For unit_status
    public $ip_address;
    public $conn_status;
    public $message_time;
    public $message_type;
    public $gps_dup_ct;
    public $soft_ver;
    public $sim_no;

    function __construct($build_code)
    {
        global $rtpiconnector;

        /* When creating an active item, if the build code is AUT (scheduled only vehicle), then
           no build exists so just create it empty */
        $this->unit_build = new UnitBuild($rtpiconnector);

        if ( $build_code == "AUT" )
        {
            $this->unit_build->build_code = "AUT";
            $this->unit_build->ip_address = "127.0.0.1";
            $this->unit_build->conn_status = "N";
            $this->unit_build->loaded = true;
        }
        else
        {
            $this->unit_build->build_code = $build_code;
            if (!$this->unit_build->load(array("build_code")))
                echo "ActiveItem->__construct() Failed to find build for code $build_code\n";
    //        else
    //            echo "ActiveItem->__construct() successfully loaded unit_build with build_id " . $this->unit_build->build_id . "\n";
        }
        
    }

    function show()
    {
        echo "ActiveItem->show() this should be overridden in the child class.\n";
    }
}

?>

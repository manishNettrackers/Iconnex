<?php

require_once("ActiveItem.class.php");

class ActiveList
{
    private $list;

    function __construct()
    {
        $this->list = array();
    }

    function isActive($build_code)
    {
        if (array_key_exists($build_code))
            return true;

        return false;
    }

    /**
     * @brief Get an ActiveItem object by build_code.
     *        If the item is not in the ActiveList, then add it.
     */
    function get_active_item($build_code, $tj_list, $tjl_list)
    {
//        echo "ActiveList->get_active_item for build $build_code\n";
        foreach ($this->list as $key => $value) 
        { 
            if ($key == $build_code) 
                return $value; 
        }

        return $this->add($build_code, $tj_list, $tjl_list);
    }

    /**
     * @brief add an ActiveItem to the list.
     */
    function add($build_code, $tj_list, $tjl_list)
    {
//        echo "ActiveList->add() for build_code $build_code\n";
        global $rtpiconnector;
        $item = false;

        // If we are adding a scheduled vehicle (the build_code is AUT) 
        // then we can assume we are create a BUS type AxctiveItem
        if ( $build_code == "AUT" )
            $unit_type = "BUS";
        else
        {
            // Get the unit_type in order to create the appropriate ActiveItem
            $sql = "SELECT unit_type from unit_build where build_code = '$build_code'";
            $ret = $rtpiconnector->fetch1SQL($sql);
            if (!$ret)
            {
                echo "ActiveList->add() Failed to get unit_type of build $build_code\n";
                return NULL;
            }
            $unit_type = trim($ret["unit_type"]);
        }

        switch ($unit_type)
        {
            case "BUS":
                $item = new ActiveVehicle($build_code, $tj_list, $tjl_list);
                if ($item)
                {
                    if ( !$item->unit_build->loaded)
                        $item = false;
                }
                break;

            case "BUSSTOP":
                $item = new ActiveBusStop($build_code, $tj_list, $tjl_list);
                if ($item)
                {
                    if ( !$item->unit_build->loaded)
                        $item = false;
                }
                break;

            default:
                echo "ActiveList->add() Unsupported unit_type $unit_type\n";
                return NULL;
        }

        if ($item)
            $this->list[$item->unit_build->build_code] = $item;
            
        return $item;
    }

    /**
     * @brief Initialise the list by running through the tjl_list and adding
     *        ActiveVehicle objects for the vehicle specified in each tjl.
     */
    function load($tj_list, $tjl_list)
    {
        global $rtpiconnector;

        foreach ($tjl_list->list as $ttb_id => $tjl)
        {
            if ($tjl->vehicle_id <= 0)
            {
                echo "ActiveList->load() no vehicle for live journey\n";
                $tjl_list->delete($tjl->fact_id);
                //exit;
                continue;
            }

            $vehicle = new Vehicle($rtpiconnector);
            $vehicle->vehicle_id = $tjl->vehicle_id;
            if (!$vehicle->load())
            {
                echo "ActiveList->load() failed to load vehicle for live journey (vehicle_id " . $vehicle->vehicle_id . " - delete it TODO\n";
                $tjl_list->delete($tjl->fact_id);
                exit;
            }
            $tjl->vehicle = $vehicle;
            if ($vehicle->vehicle_code == "AUT")
                continue;

            $unit_build = new UnitBuild($rtpiconnector);
            $unit_build->build_id = $vehicle->build_id;
            if (!$unit_build->load())
            {
                echo "ActiveList->load() failed to load build for vehicle->build_id " . $vehicle->build_id . " - delete tjl TODO\n";
                $tjl_list->delete($tjl->fact_id);
                exit;
            }

            $this->get_active_item($unit_build->build_code, $tj_list, $tjl_list);
        }
    }

    function show()
    {
        echo "================================================================================\n";
        foreach ($this->list as $build_code => $active_item)
        {
            echo "$build_code: ";
            $active_item->show();
            echo "\n";
        }
        echo "================================================================================\n";
    }
}

?>

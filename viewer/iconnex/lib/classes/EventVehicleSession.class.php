<?php

require_once("EventUnit.class.php");

class EventVehicleSession extends EventUnit
{
    public $sessionVehicleIdentifier = 0;

    function process()
    {
        global $rtpiconnector;
        echo "EventVehicleSession->process()\n";
        $rtpiconnector->executeSQL("BEGIN WORK");

        // First get an appropriate active item
        $this->active_item = $this->event_handler->active_list->get_active_item($this->reference, $this->event_handler->tj_list, $this->event_handler->tjl_list);
        if (!$this->active_item)
        {
            echo "EventVehicleSession->process() failed to get_active_item - cannot process event\n";
            return;
        }

        parent::process();

                echo "ZZZ ActiveVehicle vehicle is not set\n";
        if (get_class($this->active_item) == "ActiveVehicle")
        {
            if ($this->active_item->vehicle)
            {
                $sql = "UPDATE vehicle_session
                    SET session_id = $this->sessionVehicleIdentifier
                    WHERE vehicle_id = " . $this->active_item->vehicle->vehicle_id;
                $ret = $rtpiconnector->executeSQL($sql);
            }
            else
                echo "ZZZ ActiveVehicle vehicle is not set\n";
        }
        else
            echo "ZZZ ActiveItem is not an ActiveVehicle\n";

        $rtpiconnector->executeSQL("COMMIT WORK");
    }
}

?>

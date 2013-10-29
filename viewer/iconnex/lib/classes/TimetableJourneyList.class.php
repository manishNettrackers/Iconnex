<?php

class TimetableJourneyList
{
    private $list;

    function __construct()
    {
        $this->list = array();
    }

    function add($timetable_journey)
    {
        $this->list[$timetable_journey->timetable_id] = $timetable_journey;
    }

    function show()
    {
        $i = 1;
        foreach ($this->list as $key => $value)
        {
            echo "TimetableJourneyList->show() $i: timetable_id $key\n";
            $i++;
        }
    }

    function getMatchingJourney($unit_build, $event_journey_details)
    {
        $timetable_journey = NULL;

        foreach ($this->list as $key => $tj)
        {
            if ($tj->route_code != $event_journey_details->service_code)
                break;
            if ($tj->operator_id != $unit_build->operator_id)
                break;
            if ($tj->running_no != $event_journey_details->running_board)
                break;
            if ($tj->duty_no != $event_journey_details->duty_number)
                break;
            if ($tj->etm_trip_no != $event_journey_details->journey_number)
                break;
            if ($tj->start_time != $event_journey_details->scheduled_start)
                break;
            if ($tj->direction != $event_journey_details->direction)
                break;

            $timetable_journey = $tj;
            break;
        }

        if (!$timetable_journey)
        {
            $timetable_journey = new TimetableJourney();

            if (!$timetable_journey->loadByEvent($unit_build, $event_journey_details))
            {
                echo "TimetableJourney->getMatchingJourney() loadByEvent failed to a find timetable_journey\n";
                return false;
            }

            $this->add($timetable_journey);
        }

        return $timetable_journey;
    }

    function getJourneyByTimetableID($timetable_id)
    {
        global $rtpiconnector;

        if (array_key_exists($timetable_id, $this->list))
            return $this->list[$timetable_id];

        $timetable_journey = new TimetableJourney($rtpiconnector);
        $timetable_journey->timetable_id = $timetable_id;
        if (!$timetable_journey->load(array("timetable_id")))
        {
            echo "TimetableJourneyList->getJourneyByTimetableID() failed to find journey for timetable_id $timetable_id";
            return false;
        }

        $timetable_journey->buildVisitsArray();
        $this->add($timetable_journey);
        return $timetable_journey;
    }
}

?>

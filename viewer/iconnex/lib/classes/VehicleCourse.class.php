<?php

define(MAX_POSITIONS, 4);
define(MAX_VECTORS, MAX_POSITIONS - 1);

class VehicleCourse
{
    private $positions;
    private $vectors;
    public $gpsbearing;

    function __construct()
    {
        $this->positions = array();
        $this->vectors = array();
    }

    /**
     * @brief Update the vehicle course with the latest position.
     * and generate a new vector between the last two positions.
     *
     * Maintains a history of positions and vectors between those positions.
     */
    function update($gps_position)
    {
        $prevPos = end($this->positions);
//        array_push($this->positions, $gps_position);
        $this->positions[] = $gps_position;

        if (count($this->positions) > MAX_POSITIONS)
            array_shift($this->positions);

        if ($prevPos)
        {
            $t = $gps_position->gps_time->getTimestamp() - $prevPos->gps_time->getTimestamp();
            $x = $gps_position->longitude_milliarcsecs - $prevPos->longitude_milliarcsecs;
            $y = $gps_position->latitude_milliarcsecs - $prevPos->latitude_milliarcsecs;
            //$magnitude = sqrt(pow($x, 2) + pow($y, 2)); // Pythagoras
            $magnitude = metres_between_coords($prevPos->latitude, $prevPos->longitude, $gps_position->latitude, $gps_position->longitude);

            $bearing = 0;
            if (!($x == 0 && $y == 0))
            {
                // Avoid north being along the x-axis
                $bearing = rad2deg(atan2($x, $y));
                if ($x < 0)
                    $bearing += 360;
            }

            $this->vectors[] = array(
                "x" => $x,
                "y" => $y,
                "magnitude" => $magnitude,
                "bearing" => number_format($bearing, 3),
                "interval" => $t);

            if (count($this->vectors) > MAX_VECTORS)
                array_shift($this->vectors);
        }

        $this->updateStats();

        $this->show();
    }

    /**
     * @brief Update statistics about the course of the vehicle
     *
     * Calculates an average of the bearings supplied by the GPS receiver on
     * the vehicle. Also calculates an average of the bearings of the vectors
     * created by the last few points.
     */
    function updateStats()
    {
        $ct = 0;
        $sumlat = 0;
        foreach ($this->positions as $key => $pos)
        {
            $sumbearing += $pos->bearing;
            $ct++;
        }
        $this->gpsbearing = $sumbearing / $ct;
        echo "VehicleCourse->updateStats() gpsbearing is " . $this->gpsbearing . "\n";

        $sumlat = 0;
        foreach ($this->vectors as $key => $vec)
        {
            $sumbearing += $vec["bearing"];
            $ct++;
        }
        $this->vectorbearing = $sumbearing / $ct;
        echo "VehicleCourse->updateStats() vectorbearing is " . $this->vectorbearing . "\n";
    }

    function show()
    {
        echo "VehicleCourse->show()\n";

        foreach ($this->positions as $key => $pos)
        {
            echo "POS $key: " . $pos->gps_time->format("Y-m-d H:i:s") . " " . $pos->plottableString . " " . $pos->bearing . "\n";
        }

        foreach ($this->vectors as $key => $vec)
        {
            echo "VEC $key: " . $vec["x"] . "," . $vec["y"] . " magnitude " . $vec["magnitude"] . ", bearing " . $vec["bearing"] . " took " . $vec["interval"] . "s\n";
        }
    }
}

?>

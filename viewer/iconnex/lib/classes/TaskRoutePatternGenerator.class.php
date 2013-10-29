<?php

global $workingdaystmt;

$workingdaystmt = false;

/**
** Class: TaskRoutePatternGenerator
** --------------------------------
**
** Generates, route pattern structures for routes for visualising routes
** in schematic views etc
**
*/

class TaskRoutePatternGenerator extends ScheduledTask
{

    /*
    ** runTask
    **
    ** when run as a scheduled task.
    ** Generates daily timetable records for next few days
    */
    function runTask()
    {
        // Ensure Route Pattern Generation Table exists
        $routegen = new RoutePatternGeneration($this->connector);
        if ( !$routegen->tableExists() )
        {
            $routegen->createTable();
        }

        // Build a list of routes to generate patterns for ... those that have recently been imported or those
        // which have never been generated
        $sql = "SELECT route_id FROM route
                         WHERE route_id NOT IN ( SELECT route_id FROM route_pattern_generation )
                         OR route_code matches '*'
                         UNION ALL
                         SELECT a.route_id FROM route_pattern_generation a
                         JOIN route_import_status b ON a.route_id = b.route_id
                         AND generate_time <= import_time";
                         
        if ( !($stmt = $this->connector->executeSQL($sql)) )
        {
            echo "Cant generate route generation list\n";
            return;
        }
        
        while ( $row = $stmt->fetch() )
        {
                $this->odsconnector->executeSQL("BEGIN WORK");
                if ( !$this->generate_route_pattern( $row["route_id"]) )
                {
                    echo "Pattern Failed for Route ".$row["route_id"]."!!!!\n";
                    $this->odsconnector->executeSQL("ROLLBACK WORK");
                    continue;
                }
                $this->storeGenerationtime($row["route_id"]);
                $this->odsconnector->executeSQL("COMMIT WORK");
        }
    }

    /*
    ** Stores the fact that we have generated a route pattern in the route_pattern_generation table
    */
    function storeGenerationtime($route_id)
    {
        $routegen = new RoutePatternGeneration($this->connector);
        $routegen->route_id = $route_id;
        $routegen->generate_time =  UtilityDateTime::currentTime();
        if ( !$routegen->load() )
        {
            $routegen->generate_time =  UtilityDateTime::currentTime();
            $routegen->add();
        }
        else
        {
            $routegen->generate_time =  UtilityDateTime::currentTime();
            $routegen->save();
        }
    }


    /*
    ** generate_route_pattern
    **
    ** Passed through all locations in the passed route and
    ** tries to work a natural order for represeantion in despathcer line
    ** views and reports
    */
    function generate_route_pattern( $in_route_id )
    {
        $in_debug = 0;

        $this->connector->executeSQL("DROP TABLE t_locseq", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_locord", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_locord_srv", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_locs", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_locseq", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_locseq", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_srvct", "CONTINUE");
        $this->connector->executeSQL("DROP TABLE t_timing_pt", "CONTINUE");
        $this->connector->executeSQL("
            CREATE TEMP TABLE t_locseq
            (
                seq			INTEGER,
                serv		INTEGER,
                loc1		INTEGER,
                loc2		INTEGER,
                loc3		INTEGER,
                dir			INTEGER,
                bch			INTEGER,
                ord			INTEGER
            ) WITH NO LOG");

        $this->connector->executeSQL("
            CREATE TEMP TABLE t_locord
            (
                seq			INTEGER,
                serv		INTEGER,
                loc1		INTEGER,
                loc2		INTEGER,
                loc3		INTEGER,
                dir			INTEGER,
                bch			INTEGER,
                ord			INTEGER

            ) WITH NO LOG
            ");

        $this->connector->executeSQL("
            CREATE TEMP TABLE t_locord_srv
            (
                seq			INTEGER,
                serv		INTEGER,
                loc1		INTEGER,
                loc2		INTEGER,
                loc3		INTEGER,
                dir			INTEGER,
                bch			INTEGER,
                ord			INTEGER

            ) WITH NO LOG
            ");

        $this->connector->executeSQL("
            CREATE TEMP TABLE t_locs
            (
                seq			INTEGER,
                serv		INTEGER,
                loc1		INTEGER,
                loc2		INTEGER,
                loc3		INTEGER,
                dir			INTEGER,
                bch			INTEGER,
                ord			INTEGER

            ) WITH NO LOG
            ");

        $sql = 
            "SELECT service.service_id, service_code, direction, rpat_orderby, location_id 
             FROM service, service_patt
             WHERE service.service_id = service_patt.service_id
               AND TODAY BETWEEN wef_date AND wet_date
               AND service_code != 'FULL'
               AND route_id = $in_route_id
               ORDER BY 1, 3, 4";


        $o1_serv = array();
        $o2_serv = array();

        $ct = 0;

        $stmt = $this->connector->executeSQL($sql);
        while ( $service = $stmt->fetch() )
        {
            $ct++;
            if ( !$o2_serv ) {
                if ( $in_debug ) {
                    // echo "First ignore"
                }
                $o2_serv = $service;
                continue;
            }

            if  ( !$o1_serv ) {
                if ( $in_debug ) {
                // echo "second ignore"
                }
                $w_locs["serv"] = $o2_serv["service_id"];
                $w_locs["loc1"] = 0;
                $w_locs["loc2"] = $o2_serv["location_id"];
                $w_locs["loc3"] = $service["location_id"];
                $w_locs["ord"] = $o2_serv["rpat_orderby"];
                $w_locs["dir"] = $o2_serv["direction"];
                $w_locs["seq"] = $ct;
                $w_locs["bch"] = false;
                $sql = "INSERT INTO t_locs VALUES (
                            ".$w_locs["seq"].",
                            ".$w_locs["serv"].",
                            ".$w_locs["loc1"].",
                            ".$w_locs["loc2"].",
                            ".$w_locs["loc3"].",
                            ".$w_locs["dir"].",
                            ".$this->connector->valueToDBValue($w_locs["bch"]).",
                            ".$w_locs["ord"]." )";
                $this->connector->executeSQL($sql);
                $o1_serv = $o2_serv;
                $o2_serv = $service;
                continue;
            }

            if ( $service["service_id"] != $o2_serv["service_id"] ) {

                $w_locs["serv"] = $o2_serv["service_id"];
                $w_locs["loc1"] = $o1_serv["location_id"];
                $w_locs["loc2"] = $o2_serv["location_id"];
                $w_locs["loc3"] = 0;
                $w_locs["ord"] = $o2_serv["rpat_orderby"];
                $w_locs["dir"] = $o2_serv["direction"];
                $w_locs["seq"] = $ct;
                $w_locs["bch"] = false;
                $sql = "INSERT INTO t_locs VALUES (
                            ".$w_locs["seq"].",
                            ".$w_locs["serv"].",
                            ".$w_locs["loc1"].",
                            ".$w_locs["loc2"].",
                            ".$w_locs["loc3"].",
                            ".$w_locs["dir"].",
                            ".$this->connector->valueToDBValue($w_locs["bch"]).",
                            ".$w_locs["ord"]." )";
                $this->connector->executeSQL($sql);

                $o1_serv = false;
                $o2_serv = $service;
                continue;
            }

            if ( $in_debug ) {
                //echo "All three", $o1_serv["location_id"], $service["location_id"]
            }
            $w_locs["serv"] = $o2_serv["service_id"];
            $w_locs["loc1"] = $o1_serv["location_id"];
            $w_locs["loc2"] = $o2_serv["location_id"];
            $w_locs["loc3"] = $service["location_id"];
            $w_locs["ord"] = $o2_serv["rpat_orderby"];
            $w_locs["dir"] = $o2_serv["direction"];
            $w_locs["seq"] = $ct;
                $w_locs["bch"] = false;
                $sql = "INSERT INTO t_locs VALUES (
                            ".$w_locs["seq"].",
                            ".$w_locs["serv"].",
                            ".$w_locs["loc1"].",
                            ".$w_locs["loc2"].",
                            ".$w_locs["loc3"].",
                            ".$w_locs["dir"].",
                            ".$this->connector->valueToDBValue($w_locs["bch"]).",
                            ".$w_locs["ord"]." )";
                $this->connector->executeSQL($sql);


            $o1_serv = $o2_serv;
            $o2_serv = $service;

        }
        if ( $ct == 0 )
        {
            echo "Error - no patterns for route $in_route_id\n";
            return;
        }
        
        $w_locs["serv"] = $o2_serv["service_id"];
        $w_locs["loc1"] = $o1_serv["location_id"];
        $w_locs["loc2"] = $o2_serv["location_id"];
        $w_locs["loc3"] = 0;
        $w_locs["ord"] = $o2_serv["rpat_orderby"];
        $w_locs["dir"] = $o2_serv["direction"];
        $w_locs["seq"] = $ct + 1;
        $sql = "INSERT INTO t_locs VALUES (
                            ".$w_locs["seq"].",
                            ".$w_locs["serv"].",
                            ".$w_locs["loc1"].",
                            ".$w_locs["loc2"].",
                            ".$w_locs["loc3"].",
                            ".$w_locs["dir"].",
                            ".$this->connector->valueToDBValue($w_locs["bch"]).",
                            ".$w_locs["ord"]." )";
        $this->connector->executeSQL($sql);

        $sql = "
        SELECT serv, dir, count(*) cnt
          FROM t_locs
         GROUP BY 1, 2
        INTO TEMP t_srvct WITH NO LOG";
        $this->connector->executeSQL($sql);

        $sql = "
            SELECT UNIQUE dir 
              FROM t_srvct";
        $c_dirs = $this->connector->executeSQL($sql);


        // DELETE FROM t_locs WHERE loc1 = 0 || $loc3 = 0
        while ( ( $dirs = $c_dirs->fetch()) )
        {
            $l_dir = $dirs["dir"];

            $ct = 1;
            $cbch = 0;

            $sql = "
                SELECT *
                FROM t_srvct
                WHERE dir = ".$l_dir["dir"]."
                ORDER BY dir, cnt DESC";
            $c_dirsrv = $this->connector->executeSQL($sql);

            while ( ( $w_srvct = $c_dirsrv->fetch()) )
            {
                $ct2 = 1;
                $canny = 1;
                while ( $ct2 > 0 )
                {
                    //select count(*) into selct from t_locs
                    //if ( $in_debug ) {
                    //echo "-------", ct, ct2, selct, "? ? ", $w_srvct["serv"]
                    //}
                    $ct2 = 0;
                    $branched_yet = false;
                    $sql = "
                        SELECT *
                        FROM t_locs
                        WHERE serv = ".$w_srvct["serv"]."
                        ORDER BY ord ";
                    $c_loc1 = $this->connector->executeSQL($sql);
                    while ( ( $w_locs = $c_loc1->fetch()) )
                    {
                        $w_locs["bch"] = $cbch;
                        $ct2 = $ct2 + 1;

                        if ( $ct == 1 ) {
                            $sql = "INSERT INTO t_locord VALUES (
                                        ".$w_locs["seq"].",
                                        ".$w_locs["serv"].",
                                        ".$w_locs["loc1"].",
                                        ".$w_locs["loc2"].",
                                        ".$w_locs["loc3"].",
                                        ".$w_locs["dir"].",
                                        ".$w_locs["bch"].",
                                        ".$w_locs["ord"]." )";
                            $this->connector->executeSQL($sql);

                            $sql = "INSERT INTO t_locord_srv VALUES (
                                        ".$w_locs["seq"].",
                                        ".$w_locs["serv"].",
                                        ".$w_locs["loc1"].",
                                        ".$w_locs["loc2"].",
                                        ".$w_locs["loc3"].",
                                        ".$w_locs["dir"].",
                                        ".$w_locs["bch"].",
                                        ".$w_locs["ord"]." )";
                            $this->connector->executeSQL($sql);

                            $sql = "DELETE FROM  t_locs 
                                WHERE t_locs.serv = ".$w_locs["serv"]."
                                AND t_locs.ord = ".$w_locs["ord"];
                            $this->connector->executeSQL($sql);
                            if ( $in_debug ) {
                                echo "** D1". $w_locs["loc1"]." ". $w_locs["loc2"]." ". $w_locs["loc3"]."\n";
                            }
                            $canny = 0;
                            continue;
                        }

                        // -------------------------------------------------
                        // All 3 match  - Duplicate
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) selct
                            FROM t_locord
                            WHERE dir = $l_dir
                            AND loc1 = ".$w_locs["loc1"]."
                            AND loc2 = ".$w_locs["loc2"]."
                            AND loc3 = ".$w_locs["loc3"];
                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["selct"];

                        // && ( loc1 = $w_locs["loc1"] || $w_locs["loc"]1 = 0 )
                        // && $loc2 = $w_locs["loc2"]
                        // && ( loc3 = $w_locs["loc3"] || $w_locs["loc"]3 = 0 )

                        if ( $selct > 0 ) {
                            if ( $in_debug ) {
                             //echo "** D2 All 3 match". $w_locs["loc1"]." ". $w_locs["loc2"]." ". $w_locs["loc3"]."\n";
                            }
                            $sql = "INSERT INTO t_locord_srv VALUES (
                                        ".$w_locs["seq"].",
                                        ".$w_locs["serv"].",
                                        ".$w_locs["loc1"].",
                                        ".$w_locs["loc2"].",
                                        ".$w_locs["loc3"].",
                                        ".$w_locs["dir"].",
                                        ".$w_locs["bch"].",
                                        ".$w_locs["ord"]." )";
                            $this->connector->executeSQL($sql);
                            $sql = "DELETE FROM  t_locs 
                                WHERE t_locs.serv = ".$w_locs["serv"]."
                                AND t_locs.ord = ".$w_locs["ord"];
                            $this->connector->executeSQL($sql);
                            $canny = 0;
                            continue;
                        }

                        // -------------------------------------------------
                        // Follow on from existing end sequence of two
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) ct, MAX(seq) seq, MAX(bch) bch
                        FROM t_locord
                        WHERE dir = $l_dir
                        AND loc2 = ".$w_locs["loc1"]."
                        AND loc3 = ".$w_locs["loc2"]."
                        AND loc3 != 0";

                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["ct"];
                        $m_seq = $ret["seq"];
                        $l_bch = $ret["bch"];

                        if ( $selct > 0 ) {
                            if ( $in_debug ) {
                            echo "** Follow on from end 2". " ". $w_locs["loc1"]. " ". $w_locs["loc2"]. " ". $w_locs["loc3"]." ". $l_bch."\n";
                            }
                            $w_locs["seq"] =  $m_seq + 1;
                            if ( !$branched_yet  ) {
                                $cbch = $cbch + 1;
                                $branched_yet = true;
                            }
                            $w_locs["bch"] =  $cbch;
                            $this->apply_pattern($w_locs);
                            $canny = 0;
                            continue;
                        }
        
                        // -------------------------------------------------
                        // Join to existing start sequence of two
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) ct, MAX(seq) seq, MAX(bch) bch
                        FROM t_locord
                        WHERE dir = $l_dir
                        AND loc2 = ".$w_locs["loc2"]."
                        AND loc3 = ".$w_locs["loc3"]."
                        AND loc3 != 0";

                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["ct"];
                        $m_seq = $ret["seq"];
                        $l_bch = $ret["bch"];


                        if ( $selct > 0 ) {
                            if ( $in_debug ) {
                            echo "** Join to start two". $w_locs["loc1"]." ". $w_locs["loc2"]." ". $w_locs["loc3"]."\n";
                            }
                            $w_locs["seq"] =  $m_seq - 1;
                            if ( !$branched_yet  ) {
                                $cbch = $cbch + 1;
                                $branched_yet = true;
                            }
                            $w_locs["bch"] =  $cbch;
                            $this->apply_pattern($w_locs);
                            $canny = 0;
                            continue;
                        }
        
                        // -------------------------------------------------
                        // Join to  existing end sequence of two
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) ct, MAX(seq) seq
                        FROM t_locord
                        WHERE dir = $l_dir
                        AND loc2 = ".$w_locs["loc2"]."
                        AND loc3 = ".$w_locs["loc3"]."
                        AND loc3 != 0";

                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["ct"];
                        $m_seq = $ret["seq"];

                        if ( $selct > 0 ) {
                            if ( $in_debug ) {
                            echo "** Join to end two". $w_locs["loc1"]." ". $w_locs["loc2"]." ". $w_locs["loc3"]."\n";
                            }
                            $w_locs["seq"] =  $m_seq;
                            $this->apply_pattern($w_locs);
                            $canny = 0;
                            continue;
                        }
        
                        // -------------------------------------------------
                        // Follow on exsiting start sequence of two
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) ct, MIN(seq) seq
                        FROM t_locord
                        WHERE dir = $l_dir
                        AND loc1 = ".$w_locs["loc1"]."
                        AND loc2 = ".$w_locs["loc2"]."
                        AND loc1 != 0";

                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["ct"];
                        $m_seq = $ret["seq"];

                        if ( $selct > 0 ) {
                            if ( $in_debug ) {
                            echo "** Follow on from start 2". $w_locs["loc1"]." ". $w_locs["loc2"]." ". $w_locs["loc3"]."\n";
                            }
                            $w_locs["seq"] =  $m_seq;
                            $this->apply_pattern($w_locs);
                            $canny = 0;
                            continue;
                        }
        
                        // -------------------------------------------------
                        // Follow on existing end sequence of 1
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) ct, MIN(seq) seq, MAX(bch) bch
                        FROM t_locord
                        WHERE dir = $l_dir
                        AND loc3 = ".$w_locs["loc1"]."
                        AND loc3 != 0";

                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["ct"];
                        $m_seq = $ret["seq"];
                        $l_bch = $ret["bch"];
        
                        if ( $selct > 0 ) {
                            if ( $in_debug ) {
                            echo "** D5 Follow on end of 1",  $w_locs["loc1"].",". $w_locs["loc2"].",". $w_locs["loc3"].","."\n";
                            }
                            $w_locs["seq"] =  $m_seq + 2;

                            if ( !$branched_yet  ) {
                                $cbch = $cbch + 1;
                                $branched_yet = true;
                            }
                            $w_locs["bch"] =  $cbch;
                            $this->apply_pattern($w_locs);
                            $canny = 0;
                            continue;
                        }
        
                        // -------------------------------------------------
                        // Join to exsiting start sequence of 1
                        // -------------------------------------------------
                        $sql = "SELECT COUNT(*) ct, MAX(seq) seq, MAX(bch) bch
                        FROM t_locord
                        WHERE dir = $l_dir
                        AND loc1 = ".$w_locs["loc3"]."
                        AND loc1 != 0";

                        $ret = $this->connector->fetch1SQL($sql);
                        $selct = $ret["ct"];
                        $m_seq = $ret["seq"];
                        $l_bch = $ret["bch"];
        
        
                        if ( $selct > 0 ) {
                            $w_locs["seq"] =  $m_seq - 1;
                            if ( !$branched_yet  ) {
                                $cbch = $cbch + 1;
                                $branched_yet = true;
                            }
                            $w_locs["bch"] =  $cbch;
                            $this->apply_pattern($w_locs);
                            //if ( $in_debug ) {
                            //echo "** D6"; 
                            //var_dump($w_locs);
                            //echo ", $sqlca["sqlerrd"][3]".
                            //}
                            $canny = 0;
                            continue;
                        }

                        // -------------------------------------------------
                        // No match found
                        // -------------------------------------------------
                        if ( $canny > 100 ) {
                            $this->apply_pattern($w_locs);
                            if ( $in_debug ) {
                            echo "** D7 ",  $w_locs["loc1"].",". $w_locs["loc2"].",". $w_locs["loc3"].","."\n";
                            }
                        }
        
                    }
                    if ( $canny > 0 ) {
                        $canny = 500;
                    }
                    $canny = $canny + 1;
                    if ( $ct2 == 0 ) {
                        break;
                    }
                }

                $ct = $ct + 1;

            }

        }

        if ( $ct == 0 )
        {
            echo "Error - no patterns for route $in_route_id\n";
            return;
        }

        // New table for locations
        // For each direction/branch
        // Find start of branch || $end of branch in existing set &&
        // insert appropriately

        $sql = "SELECT dir, bch, min(seq) minseq, max(seq) maxseq
        FROM t_locord, outer location a, outer location b, outer location c
        WHERE t_locord.loc1 = a.location_id
        AND t_locord.loc2 = b.location_id
        AND t_locord.loc3 = c.location_id
        GROUP BY dir, bch
        ORDER BY dir, bch";
        $c_locseq = $this->connector->executeSQL($sql);


        while ( $locseq = $c_locseq->fetch() )
        {
            $l_dir = $locseq["dir"];
            $l_bch = $locseq["bch"];
            $l_seq_min = $locseq["minseq"];
            $l_seq_max = $locseq["maxseq"];
            
            if ( $in_debug ) {
            echo "** ". $l_dir." ". $l_bch." ". $l_seq_min." ". $l_seq_max."\n";
            }
            $l_seq = 1;


            $sql = "SELECT *, rowid
              FROM t_locord
              WHERE dir = $l_dir
                AND bch = $l_bch
              ORDER BY seq ";
            $c_bchseq = $this->connector->executeSQL($sql);

            while ( $bchseq = $c_bchseq->fetch() )
            {
                $w_locs = $bchseq;
                $l_rowid = $bchseq["rowid"];                

                $l_loc1 = $this->connector->fetch1ValueSQL("SELECT description from location where location_id = ".$w_locs["loc1"]);
                $l_loc2 = $this->connector->fetch1ValueSQL("SELECT description from location where location_id = ".$w_locs["loc2"]);
                $l_loc3 = $this->connector->fetch1ValueSQL("SELECT description from location where location_id = ".$w_locs["loc3"]);

                $l_seq1 = false;
                $l_seq2 = false;

                $l_seq1 = $this->connector->fetch1ValueSQL("SELECT min(seq) seq FROM t_locseq WHERE dir = $l_dir AND loc2 = ".$w_locs["loc1"]);
                $l_seq2 = $this->connector->fetch1ValueSQL("SELECT max(seq) seq FROM t_locseq WHERE dir = $l_dir AND loc2 = ".$w_locs["loc3"]);
                $l_seq0 = $this->connector->fetch1ValueSQL("SELECT max(seq) seq FROM t_locseq WHERE dir = $l_dir AND loc2 = ".$w_locs["loc2"]);

                if ( $in_debug ) {
                // echo "    ** ", l_seq1, l_seq2
                }
                if ( $l_seq0  ) {
                    if ( $in_debug ) {
                    echo "Already there ".$w_locs["bch"]."0 = ". $l_seq0.",". $l_loc2."\n";
                    }
                    $w_locseq = $w_locs;
                    $w_locseq["seq"] = $l_seq0;
                    $this->apply_locseq($w_locseq);
                    continue;
                }

                if ( !$l_seq1 && !$l_seq2 ) {
                    $w_locseq = $w_locs;
                    $w_locseq["seq"] = $l_seq;
                    if ( $in_debug ) {
                    echo "Nothing ".$w_locs["bch"]."1 = ". $l_seq1.",". " 2 = ", $l_seq2.",". $l_loc2."\n";
                    }
                    $this->apply_locseq($w_locseq);
                    continue;
                }

                if ( $l_seq1  && !$l_seq2) {
                    $w_locseq = $w_locs;
                    $w_locseq["seq"] = $l_seq1 + 1;
                    if ( $in_debug ) {
                    echo "F1 ".$w_locs["bch"]."1 = ". $l_seq1.",". " 2 = ", $l_seq2.",". $l_loc2."\n";
                    }
                    $this->apply_locseq($w_locseq);
                    continue;
                }

                if ( !$l_seq1 && $l_seq2  ) {
                    $w_locseq = $w_locs;
                    $w_locseq["seq"] = $l_seq2 - 1;
                    if ( $in_debug ) {
                    echo "F2 ".$w_locs["bch"]."1 = ". $l_seq1.",". " 2 = ", $l_seq2.",". $l_loc2."\n";
                    }
                    $this->apply_locseq($w_locseq);
                    continue;
                }

                if ( $l_seq1  && $l_seq2  ) {
                    $w_locseq = $w_locs;
                    $w_locseq["seq"] = $l_seq2 + 1;
                    if ( $in_debug ) {
                    echo "F3 ",$w_locs["bch"],"1 = ", $l_seq1.",". " 2 = ". $l_seq2."\n";
                    }
                    $this->apply_locseq($w_locseq);
                    continue;
                }

            }

            // Reorder sequences
            $l_seq = 1000;
            $o_seq = false;
            $sql = 
            "SELECT *, rowid
              FROM t_locseq
              WHERE dir = $l_dir
              ORDER BY seq ";
            $c_bchreseq = $this->connector->executeSQL($sql);

            while ( $bchreseq = $c_bchreseq->fetch() )
            {
                $w_locs = $bchreseq;
                $l_rowid = $bchreseq["rowid"];

                $l_loc1 = $this->connector->fetch1ValueSQL("SELECT description from location where location_id = ".$w_locs["loc1"]);
                $l_loc2 = $this->connector->fetch1ValueSQL("SELECT description from location where location_id = ".$w_locs["loc2"]);
                $l_loc3 = $this->connector->fetch1ValueSQL("SELECT description from location where location_id = ".$w_locs["loc3"]);
            
                if ( !$o_seq || $o_seq != $w_locs["seq"] OR $o_loc2 != $l_loc2 ) {
                    $l_seq = $l_seq + 1000;
                }

                $this->connector->executeSQL(
                "UPDATE t_locseq 
                    SET seq = $l_seq 
                    WHERE rowid = $l_rowid");

                if ( $in_debug ) {
                    echo  "*R* ". $l_seq. " ". $l_loc2."\n";
                }

                $o_seq = $w_locs["seq"];
                $o_loc2 = $l_loc2;

            }

        }

        $sql = "
            SELECT dir, seq, b.location_code, 
                a.description desc1, b.description desc2, c.description desc3, 
                a.location_id locid1, b.location_id locid2, c.location_id locid3, 
                bch
            FROM t_locseq, outer location a, outer location b, outer location c 
            WHERE t_locseq.loc1 = a.location_id
            AND t_locseq.loc2 = b.location_id
            AND t_locseq.loc3 = c.location_id
            ORDER BY dir, seq, bch, b.location_id, bch";
        $c_seqsel = $this->connector->executeSQL($sql);

        $this->connector->executeSQL(
        "DELETE FROM route_patt_loc WHERE rpat_id IN
            ( 
                SELECT rpat_id 
                  FROM route_pattern
                 WHERE route_id = $in_route_id
            )");

        $this->connector->executeSQL("DELETE FROM route_pattern WHERE route_id = $in_route_id");
        $o_seq = false;
        $o_loc2 = false;

        while ( $ret = $c_seqsel->fetch() )
        {
            
            $l_dir = $ret["dir"];
            $l_seq = $ret["seq"];
            $l_loccode  = $ret["location_code"];
            $l_loc1 = $ret["desc1"];
            $l_loc2 = $ret["desc2"];
            $l_loc3  = $ret["desc3"];
            $l_locid1 = $ret["locid1"];
            $l_locid2 = $ret["locid2"];
            $l_locid3  = $ret["locid3"];
            $l_seq1 = $ret["bch"];


            //echo $l_locid1.",". $l_locid2.",". $l_locid3.",". $l_seq."\n";
            if ( !$o_seq || $o_seq != $l_seq || $o_loc2 != $l_locid2 ) {
                if ( $o_seq  && $o_seq == $l_seq ) {
                    $l_seq = $l_seq + 500;
                    echo "DUP!!! => ", $l_seq."\n";
                }
                $w_rpat["rpat_id"] = 0;
                $w_rpat["route_id"]	 = $in_route_id;
                $w_rpat["sequence"] = $l_seq;
                $w_rpat["location_id"] = $l_locid2;
                $w_rpat["grid_x"] = -1;
                $w_rpat["grid_y"] = -1;
                $w_rpat["direction"] = $l_dir;
                $w_rpat["display_order"] = $l_seq;
                $w_rpat["display_dir"] = $l_dir;
                $w_rpat["node_type"] = "BS";
                //echo "^^ ", $w_rpat["display_order"]. "= ". $l_locid2;
                $sql = 
                "INSERT INTO route_pattern ( rpat_id, route_id, sequence, location_id, grid_x, grid_y, direction, display_order, display_dir ) VALUES ( ".
                $w_rpat["rpat_id"].",".
                $w_rpat["route_id"]	.",".
                $w_rpat["sequence"].",".
                $w_rpat["location_id"].",".
                $w_rpat["grid_x"].",".
                $w_rpat["grid_y"].",".
                $w_rpat["direction"].",".
                $w_rpat["display_order"].",".
                $w_rpat["display_dir"].")";

                $this->connector->executeSQL($sql);

                $w_rpat["rpat_id"] = $this->connector->lastInsertId("route_pattern");
                echo $l_dir.
                    " ",
                    $l_seq .
                    " ".
                    $l_seq1. 
                    " ".
                    $l_loccode.
                    " ".
                    $l_loc2."\n";

            } else {
                echo "          ". $l_seq1 . " ".
                                substr($l_loc1,0,19). " X ". $l_loc3. " ". 
                                $l_loccode."\n";

            }

            $w_rpat_loc["rpat_id"] = $w_rpat["rpat_id"];
            $w_rpat_loc["location_id"] = $l_locid2;
            $w_rpat_loc["loc_from"] = $l_locid1;
            $w_rpat_loc["loc_to"] = $l_locid3;
            $w_rpat_loc["branch"] = $l_seq1;
            $sql = "INSERT INTO route_patt_loc VALUES ( ".
                $w_rpat_loc["rpat_id"].",".
                $w_rpat_loc["location_id"].",".
                $this->connector->valueToDBValue($w_rpat_loc["loc_from"]).",".
                $this->connector->valueToDBValue($w_rpat_loc["loc_to"]).",".
                $w_rpat_loc["branch"].")";
            $this->connector->executeSQL($sql);

            $o_seq = $l_seq;
            $o_loc2 = $l_locid2;

        }

        $sql = 
            "SELECT *
              FROM route_pattern
             WHERE route_id = $in_route_id
             ORDER BY direction, sequence";
        $c_rpat = $this->connector->executeSQL($sql);
            

        $sql = "SELECT MIN(direction) mindir, MAX(direction) maxdir, COUNT(*) ct
          FROM route_pattern
         WHERE route_id = $in_route_id";
        $ret = $this->connector->fetch1SQL($sql);
        $l_min_dir = $ret["mindir"];
        $l_max_dir = $ret["maxdir"];
        $selct = $ret["ct"];

        // if ( $this route pattern has only one direction ) { auto generate
        // outbound && $inbound splitting it in the middle
        if ( $l_min_dir == $l_max_dir ) {
            $ct = 0;
            while ( $wr_pat = $c_rpat->fetch() )
            {
                $ct = $ct + 1;
                if ( $ct <= ( $selct / 2 ) ) {
                    $this->connector->executeSQL("UPDATE route_pattern SET direction = 0 WHERE rpat_id = ".$w_rpat["rpat_id"]);
                } else {
                    $this->connector->executeSQL("UPDATE route_pattern SET direction = 1 WHERE rpat_id = ".$w_rpat["rpat_id"]);
                }

            }
        }

        // Now flagg which ones are timing points on the route - ie
        // those that have an entry in publish_time
        $this->connector->executeSQL("UPDATE route_pattern SET node_type = 'BS' WHERE route_id = $in_route_id");

        $this->connector->executeSQL("
        SELECT unique d.location_id
        FROM publish_tt b, service c, publish_time d
        WHERE b.pub_ttb_id = d.pub_ttb_id
        AND c.service_id = b.service_id
        AND c.route_id = $in_route_id
        INTO temp t_timing_pt with no log
        ");

        $this->connector->executeSQL("
        UPDATE route_pattern SET node_type = 'TP'
        WHERE location_id IN ( SELECT location_id FROM t_timing_pt )
        AND route_id = $in_route_id
        ");

        $sql = 
            "SELECT *
              FROM route_pattern
             WHERE route_id = $in_route_id
             ORDER BY direction, sequence";
        $c_rpat2 = $this->connector->executeSQL($sql);
            
        while ( $wr_pat = $c_rpat2->fetch() )
        {
            echo $wr_pat["sequence"]. ". ".
                    $wr_pat["direction"]. " ". 
                    $wr_pat["node_type"]. " ". 
                    $wr_pat["location_id"]."\n";
        }

        $sql = 
            "SELECT a.rpat_id, t_locord_srv.serv, t_locord_srv.loc1, t_locord_srv.loc2, t_locord_srv.loc3, display_order
             FROM route_pattern a, route_patt_loc b, t_locord_srv
            WHERE a.rpat_id = b.rpat_id
              AND (
                    ( t_locord_srv.loc1 = 0  AND b.location_id = t_locord_srv.loc2 AND b.loc_to = t_locord_srv.loc3 )
                    OR ( b.loc_from = t_locord_srv.loc1 AND b.location_id = t_locord_srv.loc2 AND b.loc_to = t_locord_srv.loc3 )
                    OR ( b.loc_from = t_locord_srv.loc1 AND b.location_id = t_locord_srv.loc2 AND t_locord_srv.loc3  = 0 )
                    )
              AND a.route_id = $in_route_id";
        $c_srvlinks = $this->connector->executeSQL($sql);

        while ( $ret = $c_srvlinks->fetch() )
        {
            $l_rpat = $ret["rpat_id"];
            $l_service_id = $ret["serv"];
            $l_loc1 = $ret["loc1"];
            $l_loc2 = $ret["loc2"];
            $l_loc3 = $ret["loc3"];
            //echo $l_rpat." ". $l_service_id. "," .$l_loc1."," . $l_loc2."," . $l_loc3."\n";
            $this->connector->executeSQL(
            "UPDATE service_patt set rpat_id = $l_rpat 
                WHERE service_id = $l_service_id
                AND location_id = $l_loc2");
        }	
        return true;
        
    }

    function apply_pattern($w_locs )
    {
                            $sql = "INSERT INTO t_locord VALUES (
                                        ".$w_locs["seq"].",
                                        ".$w_locs["serv"].",
                                        ".$w_locs["loc1"].",
                                        ".$w_locs["loc2"].",
                                        ".$w_locs["loc3"].",
                                        ".$w_locs["dir"].",
                                        ".$w_locs["bch"].",
                                        ".$w_locs["ord"]." )";
                            $this->connector->executeSQL($sql);

                            $sql = "INSERT INTO t_locord_srv VALUES (
                                        ".$w_locs["seq"].",
                                        ".$w_locs["serv"].",
                                        ".$w_locs["loc1"].",
                                        ".$w_locs["loc2"].",
                                        ".$w_locs["loc3"].",
                                        ".$w_locs["dir"].",
                                        ".$w_locs["bch"].",
                                        ".$w_locs["ord"]." )";
                            $this->connector->executeSQL($sql);

                            $sql = "DELETE FROM  t_locs 
                                WHERE t_locs.serv = ".$w_locs["serv"]."
                                AND t_locs.ord = ".$w_locs["ord"];
                            $this->connector->executeSQL($sql);
    }
    function apply_locseq($w_locseq )
    {
                $sql = "INSERT INTO t_locseq VALUES (
                                        ".$w_locseq["seq"].",
                                        ".$w_locseq["serv"].",
                                        ".$w_locseq["loc1"].",
                                        ".$w_locseq["loc2"].",
                                        ".$w_locseq["loc3"].",
                                        ".$w_locseq["dir"].",
                                        ".$w_locseq["bch"].",
                                        ".$w_locseq["ord"]." )";
                            $this->connector->executeSQL($sql);
    }

}
?>

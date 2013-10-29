<?php

/* Stores user workspace */
/* Workspace session info received in workspace_session_?? parameters */
include("iconnex.php");

    global $user;
    global $sessparams;
    global $wsname;

    $user = $_criteria["user"]->get_criteria_value("VALUE", false);
    $mode = $_criteria["mode"]->get_criteria_value("VALUE", false);
    $wsname = $_criteria["workspace"]->get_criteria_value("VALUE", false);

    echo $mode;
    if ( $mode == "SAVE" )
        save_workspace($_pdo);

    $wsname = "DEFAULT";
    collate_workspace($_pdo);


function collate_workspace($pdo)
{
    global $user;
    global $sessparams;
    global $wsname;

    $iconnex = new iconnex($pdo);

    if ( !$iconnex->executeSQL("SELECT userid FROM cent_user WHERE usernm = '".$user."'") )
        return;

    $res = $iconnex->fetch();
    if ( !$res )
    {
        trigger_error ("Unknown user id");
        return;
    }

    $userid = $res["userid"];
        echo "unk $userid";

    if ( !$iconnex->executeSQL("SELECT workspace_id FROM iconnex_workspace WHERE user_id = ".$userid." AND dashboard_layout = '".$wsname."'") )
        return;

    $res = $iconnex->fetch();
    if ( !$res )
    {
        trigger_error ("Unknown workspace id");
        return;
    }

    $wsid = $res["workspace_id"];

    if ( !$iconnex->executeSQL("CREATE TEMPORARY TABLE t_ws (
                            workspace_item_id INTEGER,
                            params CHAR(255) )
                            ") )
        return;


    if ( !$iconnex->executeSQL("SELECT * FROM iconnex_workspace_item it, iconnex_workspace_parameter pr 
                            WHERE it.workspace_id = $wsid
                            AND it.workspace_item_id = pr.workspace_item_id
                            ORDER BY it.workspace_id, it.workspace_item_no
                            ") )
        return;

    $lastid = false;
    $wsitid = false;
    $paramtxt = "";
    while ( ( $res  = $iconnex->fetch() ) )
    {
        $wsitid = $res["workspace_item_id"];

        if ( !$lastid || $wsitid != $lastid )
        {
            if ( $lastid && $wsitid != $lastid )
            {
                if ( !$iconnex->executeSQL("INSERT INTO  t_ws VALUES ( $lastid, '$paramtxt' )") )
                    return;
            }
            $paramtxt = "";
        }

        $paramtxt .= "&".$res["session_parameter"]."=".$res["session_param_value"];

        $lastid = $wsitid;
    }
    if ( $lastid )
    {
        if ( !$iconnex->executeSQL("INSERT INTO  t_ws VALUES ( $lastid, '$paramtxt' )", "ERROR") )
            return;
    }

}

function save_workspace($pdo)
{
    global $user;
    global $sessparams;
    global $wsname;

    $workspace_custom_titles = array();
    $workspace_search_settings = array();
    $workspace_dashboard_tiles = array();
    $session_ct = 1;
    $workspace_name = "workspace_session_" . $session_ct;
    $workspace_menu = "workspace_title_" . $session_ct;
    $workspace_dashboard_tile_param = "workspace_dashboard_tile_" . $session_ct;
    $workspace_custom_title_param = "workspace_custom_title_" . $session_ct;
    $workspace_search_settings_param = "workspace_search_settings_" . $session_ct;

    $workspace_layout = "";
    if ( isset($_REQUEST["workspace_layout"]) ) $workspace_layout = $_REQUEST["workspace_layout"];

    $this_session = session_name();
    
    session_write_close();

    $sessparams = array();
    $sessinfo = array();
            //echo "<PRE>11";
            //var_dump($_REQUEST);
            //echo "</PRE>";

    while ( isset($_REQUEST[$workspace_name]) )
    {

        $wsparams = urldecode($_REQUEST[$workspace_name]);

        $ar = explode("&", $wsparams);

        foreach ( $ar as $k => $v )
        {
            echo "\n".$k ." = ";
            echo "<PRE>";
            var_dump($v);
            echo "</PRE>";

            $ar1 = explode("=", $v);

            if ( $ar1[0] == "session_name" )
            {
                $sess = $ar1[1];
                $workspace_custom_titles[$sess] = false;
                $workspace_search_settings[$sess] = false;
                $workspace_dash_tiles[$sess] = false;
                if ( isset($_REQUEST[$workspace_custom_title_param]) )
                    $workspace_custom_titles[$sess] = $_REQUEST[$workspace_custom_title_param];
                if ( isset($_REQUEST[$workspace_search_settings_param]) )
                    $workspace_search_settings[$sess] = $_REQUEST[$workspace_search_settings_param];
                echo $workspace_dashboard_tile_param;
                var_dump($_REQUEST);
                if ( isset($_REQUEST[$workspace_dashboard_tile_param]) )
                    $workspace_dashboard_tiles[$sess] = $_REQUEST[$workspace_dashboard_tile_param];
            //echo "<PRE>$workspace_search_settings_param ";
            //var_dump($workspace_search_settings);
            //echo "</PRE>";
                //var_dump($ar1);
                //echo "<BR> EEEEE"; echo $sess; echo "<BR>";
                session_id($sess);
                session_start();
                $sessparams[$sess] = $_SESSION;
                $sessinfo[$sess] = array();
                $sessinfo[$sess]["title"] = $_REQUEST[$workspace_menu];
                session_write_close();
            }
        }

        $wsmenu = urldecode($_REQUEST[$workspace_menu]);

        $session_ct++;
        $workspace_name = "workspace_session_" . $session_ct;
        $workspace_menu = "workspace_title_" . $session_ct;
        $workspace_custom_title_param = "workspace_custom_title_" . $session_ct;
        $workspace_search_settings_param = "workspace_search_settings_" . $session_ct;
    
    }

    session_id($this_session);
    session_start();

    $iconnex = new iconnex($pdo);

    if ( !$iconnex->executeSQL("SELECT userid FROM cent_user WHERE usernm = '".$user."'") )
        return;
    $res = $iconnex->fetch();
    if ( !$res )
    {
        trigger_error ("Unknown user id");
        return;
    }

    $userid = $res["userid"];

    if ( !$iconnex->executeSQL("SELECT workspace_id FROM iconnex_workspace WHERE user_id = ".$userid." AND workspace_name = '".$wsname."'") )
        return;

    $res = $iconnex->fetch();
    if ( $res )
    {
        $wsid = $res["workspace_id"];
        $stat = $iconnex->executeSQL("DELETE FROM iconnex_workspace_parameter WHERE workspace_id = ".$wsid."");
        if ( $stat )
            $stat = $iconnex->executeSQL("DELETE FROM iconnex_workspace_item WHERE workspace_id = ".$wsid."");
        if ( $stat )
            $stat = $iconnex->executeSQL("DELETE FROM iconnex_workspace WHERE workspace_id = ".$wsid."");
    }

    if ( !$iconnex->executeSQL("INSERT INTO iconnex_workspace VALUES ( 0, $userid, '".$wsname."', '".$workspace_layout."')" ) ) 
        return;
    $wsid = $pdo->lastInsertId();
    if ( !$wsid ) 
    {
        trigger_error ("Problem creating workspace - cause unknown");
        return;
    }

    $wsct = 1;

    foreach ($sessparams as $k => $v )
    {
        $title = $sessinfo[$k]["title"];
        var_dump($workspace_custom_titles);
        $custom_title = $workspace_custom_titles[$k];
        $search_settings = $workspace_search_settings[$k];
        var_dump($workspace_dashboard_tiles);

        if ( !isset ( $workspace_dashboard_tiles[$k] ) )
            continue;

        $dashboard_tile = $workspace_dashboard_tiles[$k];
        if ( !$iconnex->executeSQL("INSERT INTO iconnex_workspace_item VALUES ( 0, $wsid, $wsct, '".$title."', '".$custom_title."', '".$search_settings."',0, 0, 0, 0, 0, '".$dashboard_tile."')" ) ) 
            return;

        $wsitid = $pdo->lastInsertId();
        if ( !$wsitid ) 
        {
            trigger_error ("Problem creating workspace - cause unknown");
            return;
        }

        foreach ( $v as $sesk => $sesv )
        {
            //echo "$sesk = $sesv <BR>";
            if ( $sesk == "session_name" )
                continue;

            if ( $sesk == "session_name" || $sesk == "execute_mode" || preg_match ("/__/", $sesk )  )
                continue;

            if ( $sesk == "target_show_detail" || $sesk == "target_show_group_headers" || 
                $sesk == "target_show_group_trailers" || $sesk == "target_show_column_headers" 
                || $sesk == "target_show_graph" 
                || $sesk == "target_show_criteria" || $sesk == "target_show_column_headers" 
               )
                continue;
            
            if ( $sesk == "latestRequest"  )
            {
                if ( $sesv )
                foreach ( $sesv as $lsesk => $lsesv )
                {
                    if ( $lsesk == "session_name" || $lsesk == "r" || 
                        $lsesk == "target_format" || $lsesk == "execute_mode"  ||
                        $lsesk == "user" ||
                        $lsesk == "xmlin" ||
                        $lsesk == "submitPrepareData" || $lsesk == "submitPrepare"   ||
                        $lsesk == "MANUAL_since" ||
                        $lsesk == "template" )
                            continue;
                    $sql = "INSERT INTO iconnex_workspace_parameter VALUES ( $wsid, $wsitid, '". $lsesk ."', '". $lsesv ."' )";
                    if ( !$iconnex->executeSQL($sql)) 
                        return;
                }
            }
            else
            {
                $sql = "INSERT INTO iconnex_workspace_parameter VALUES ( $wsid, $wsitid, '". $sesk ."', '". $sesv ."' )";
                if ( !$iconnex->executeSQL($sql)) 
                    return;
            }
        }
        
        $wsct++;
    }

}


function show_session()
{
    echo "<PRE>";
var_dump($_SESSION);
    echo "</PRE>";
}

?>

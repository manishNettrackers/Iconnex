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

    if ( $mode == "SAVE" )
        save_workspace($_pdo);

    $wsname = "DEFAULT";
    $user = "admin";
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

    if ( !$iconnex->executeSQL("SELECT workspace_id FROM iconnex_workspace WHERE user_id = ".$userid." AND workspace_name = '".$wsname."'") )
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
                            params VARCHAR(1000) )
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
    $session_ct = 1;
    $workspace_name = "workspace_session_" . $session_ct;
    $workspace_menu = "workspace_title_" . $session_ct;

    $this_session = session_name();
    
    session_write_close();

    $sessparams = array();
    $sessinfo = array();

    while ( isset($_REQUEST[$workspace_name]) )
    {

        $wsparams = urldecode($_REQUEST[$workspace_name]);

        $ar = explode("&", $wsparams);

        foreach ( $ar as $v )
        {
            $ar1 = explode("=", $v);

            if ( $ar1[0] == "session_name" )
            {
                $sess = $ar1[1];
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

    if ( !$iconnex->executeSQL("INSERT INTO iconnex_workspace VALUES ( 0, $userid, '".$wsname."')" ) ) 
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
        if ( !$iconnex->executeSQL("INSERT INTO iconnex_workspace_item VALUES ( 0, $wsid, $wsct, '".$title."', 0, 0, 0, 0, 0)" ) ) 
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

            if ( $sesk == "session_name" || $sesk == "execute_mode" || preg_match ("/eca__/", $sesk )  )
                continue;
            
            if ( $sesk == "latestRequest"  )
            {
                if ( $sesv )
                foreach ( $sesv as $lsesk => $lsesv )
                {

                    if ( $lsesk == "session_name" || $lsesk == "r" || 
                        $lsesk == "target_format" || $lsesk == "execute_mode"  ||
                        $lsesk == "user" ||
                        $lsesk == "submitPrepareData" || $lsesk == "submitPrepare"   ||
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

<?php
/**
** Class: TaskMessageX
** --------------------------------
**
** Task that starts MessageX message handler
** from receiving messages from field units ( buses, stops etc )
** and delivers them to tasks that need them as directed in the 
** etc/MessageX.ini file
**
*/

class TaskMessageX extends ScheduledTask
{

    /*
    ** runTask
    **
    */
    function runTask()
    {
        $binpath = dirname(__FILE__);
        $inifile = $binpath."/../../etc/MessageX.ini";
        $binpath = $binpath."/../../bin";
        $cmd = $binpath."/MessageX ".$inifile;
        system($cmd);
    }
}
?>

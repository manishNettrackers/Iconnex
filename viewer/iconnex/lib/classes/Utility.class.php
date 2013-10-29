<?php

global $memory;

$memory = 0;

class Utility
{
    public $connector = false;

    function memory_increase()
    {
        global $memory;
        $mem = memory_get_usage();
        $increase = $mem - $memory;
        $memory = $mem;
        return $increase. "($mem)";
    }

    function backtrace ()
    {
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
}

?>

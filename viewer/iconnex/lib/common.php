<?php

include("config.php");

spl_autoload_extensions('.class.php');
spl_autoload_register('loadClasses');

function loadClasses($className)
{
    if (file_exists(ROOT_DIR."lib/classes/$className.class.php"))
        require_once("$className.class.php");
}

?>

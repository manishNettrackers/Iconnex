<?php

class UDPPeer
{
    public $ip = NULL;
    public $port = NULL;
    public $socket = NULL;

    function __construct($ip, $port, $sock)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->socket = $sock;
    }
}

?>

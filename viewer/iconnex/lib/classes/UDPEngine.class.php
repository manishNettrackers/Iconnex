<?php

include("UDPPeer.class.php");
include("DAIPPacket.class.php");

define("EXPECT_ICONNEX_PACKETS", "0");
define("EXPECT_DAIP_PACKETS", "1");

class UDPEngine
{
    public $expect = EXPECT_ICONNEX_PACKETS;
    public $sock = false;
    public $shutdown = false;
    public $socket_time_limit = 10;

    public $forwardsock = false;

    /**
     * @brief constructor
     */
    function __construct()
    {
        if (!extension_loaded('sockets'))
            die('ERROR: The sockets extension is not loaded.');

        set_time_limit($this->socket_time_limit);
    }

    /**
     * @brief destructor
     */
    function __destructor()
    {
        $this->shutdown();
    }

    /**
     * @brief process a received UDP packet
     */
    function process_packet($packetData, $length, $peer)
    {
        if ($this->expect == EXPECT_ICONNEX_PACKETS)
        {
            echo "ERROR: iConnex packets not supported\n";
            $this->shutdown();
            exit;
        }
        else
        {
            $packet = new DAIPPacket($packetData, $length, $peer);
            $packet->process();
        }
    }

    /**
     * @brief shutdown the UDP socket and exit
     */
    function shutdown()
    {
        if ($this->sock)
        {
            socket_set_nonblock($this->sock);
            socket_close($this->sock);
        }
        if ($this->forwardsock)
        {
            socket_close($this->forwardsock);
        }
        exit;
    }

    /*
     * @brief Create UDP receiver socket and start listening
     */
    function listen()
    {
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!socket_bind($this->sock, '0.0.0.0', 2065)) die("ERROR: Unable to bind socket");
        if (!socket_set_block($this->sock))
        {
            socket_close($this->sock);
            die('ERROR: Unable to set blocking mode for socket');
        }

        // Open a socket to the secondary server
        $this->forwardsock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->forwardsock)
            die("$errstr ($errno)");

        // Loop forever listening on the socket and processing packets
        echo "UDPEngine->listen()\n";
        while (true)
        {
            $len = socket_recvfrom($this->sock, $buf, 65535, 0, $clientIP, $clientPort);
            if ($len == false)
            {
                echo "ERROR: socket_recvfrom failed - shutting down...\n";
                $this->shutdown();
            }
            else
            {
                $peer = new UDPPeer($clientIP, $clientPort, $this->sock);
                $this->process_packet($buf, $len, $peer);

                // Forward the packet on to the secondary server
//                socket_sendto($this->forwardsock, $buf, $len, 0, "10.0.0.187", 2065);
            }
        }
    }
}
?>

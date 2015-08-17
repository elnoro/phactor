<?php
namespace Phactor\Phactor;

/**
 * Class Mailbox
 * @package Phactor\Phactor
 * Receives messages
 */
class Mailbox
{
    protected $address;
    protected $socket;

    /**
     * Constructor
     * @param integer $address an address to fetch messages for
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     * @return integer
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * creates a tcp socket if needed and accepts connection to read
     * @return resource
     */
    public function fetchMessage()
    {
        if ($this->socket === null) {
            $this->socket = stream_socket_server(static::buildFullAddress($this->address));
        }
        return @stream_socket_accept($this->socket);
    }

    /**
     * Transforms a Actor's $address into connection string for stream_socket_* functions
     * @param integer $address
     * @return string
     */
    public static function buildFullAddress($address)
    {
        return 'tcp://127.0.0.1:' . $address;
    }
}
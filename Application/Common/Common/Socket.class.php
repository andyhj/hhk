<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

/**
 * Description of Socket
 *
 * @author Administrator
 */
class Socket {

    /**
     * socket handle
     */
    public $socket = null;

    /**
     * whether debug message
     */
    public $debug = FALSE;

    /**
     * constructor
     */
    public function __construct($debug = FALSE) {
        $this->debug = $debug;
    }

    /**
     * destructor
     */
    public function __destruct() {
        if ($this->socket) {
            $this->close();
        }
    }

    /**
     * socket initialization
     */
    function init() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->debug) {
            if ($this->socket === FALSE) {
                echo "socket_create() failed, reason: " . socket_strerror(socket_last_error()) . "\n";
            } else {
                echo "OK.\n";
            }
        }
        return $this->socket;
    }

    /**
     * socket connecting
     */
    function connect($addr = '127.0.0.1', $port = 8881) {
        if ($this->debug)
            echo "Attempting to connect to '{$addr}' on port '{$port}'...";
        $result = socket_connect($this->socket, $addr, $port);
        if ($this->debug) {
            if ($result === FALSE) {
                echo "socket_connect() failed, Reason: ({$result}) " . socket_strerror(socket_last_error($this->socket)) . "\n";
            } else {
                echo "OK.\n";
            }
        }
        return $result;
    }

    /**
     * send request data
     */
    function send($data) {
        if ($this->debug)
            echo "Sending request...\n";
        $sendlen = socket_write($this->socket, $data, strlen($data));
        if ($this->debug)
            echo "OK. {$sendlen} bytes sended.\n";
        return $sendlen;
    }

    /**
     * receive data from socket server
     */
    function recv($length) {
        if ($this->debug)
            echo "Reading response:\n";
        $result = socket_read($this->socket, $length);
        if ($this->debug)
            echo $result . "\n";
        return $result;
    }

    /**
     * socket close
     */
    function close() {
        if ($this->debug)
            echo "Closing socket...\n";
        socket_close($this->socket);
        $this->socket = null;
        if ($this->debug)
            echo "OK.\n\n";
    }

}

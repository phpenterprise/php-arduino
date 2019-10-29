<?php

namespace PhpArduino;

class Arduino {

    const DEVICE_PORT = 'COM5';

    private $device, $port, $mode, $commands = array();

    function __construct($port = self::DEVICE_PORT, $mode = 'w+b') {

        // set port
        $this->setPort($port);

        // set mode
        $this->setMode($mode);

        // connect from device
        $this->connect();

        // return status
        return boolval($this->device);
    }

    public function setPort($a) {
        $this->port = $a;
    }

    public function setMode($a) {
        $this->mode = $a;
    }

    private function isWindows() {
        return (stristr(PHP_OS, 'win'));
    }

    function sendCommand($text, $command) {
        if ($this->device) {
            $this->commands[] = array($text, $command);
        }
    }

    private function output($a) {
        if ($a === "\r" OR $a === "\n") {
            echo "\n";
        } elseif ($a !== '') {
            echo $a . "\n";
        }
    }

    private function stop($a) {
        die($a);
    }

    private function wait($a = 1) {
        sleep($a);
    }

    function connect() {

        // set connection params (mode, stty)
        if ($this->isWindows()) {
            exec("MODE " . $this->port . " BAUD=9600 PARITY=n DATA=8 XON=on STOP=1");
        } else {
            exec("stty -F " . $this->port . " cs8 9600 ignbrk -brkint -icrnl -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts");
        }

        // open connection
        try {
            $this->device = fopen($this->port, $this->mode);
        } catch (Exception $e) {
            $this->stop('Device port is not accessible.');
        }

        // valid
        if (!$this->device OR ! is_resource($this->device)) {
            $this->stop('The device is not conected, port and mode on settings.');
        }

        // open stream
        stream_set_blocking($this->device, false);

        // send boot commands
        fwrite($this->device, "AT\r");
        fwrite($this->device, "AT+DDET=1\r");
        fwrite($this->device, "AT+CLIP=1\r");
        fwrite($this->device, "ATS0=2\r");
        fwrite($this->device, "AT+CMGF=1\r");
    }

    public function listen() {

        // wait
        $this->wait();

        // listen port
        while (true) {

            // read serial port data
            $data = trim($this->read());

            // validate
            if ((strlen($data) <= 1) && empty($this->commands)) {
                $this->close();
                break;
            } elseif (strlen($data) <= 1) {
                continue;
            }

            // show data
            $this->output($data);

            // check commands
            foreach ($this->commands AS $a => $b) {
                if (count($b) === 2 && stristr($data, $b[0])) {
                    fwrite($this->device, $b[1] . "\n");
                    unset($this->commands[$a]);
                }
            }

            // wait
            $this->wait();
        }
    }

    private function read() {
        if (!$this->device OR ! is_resource($this->device)) {
            return false;
        }
        $chars = [];
        do {
            $char = fread($this->device, 1);
            $chars[] = $char;
        } while ($char != "\n" && $char != "");
        return join('', $chars);
    }

    function close() {
        if ($this->device && is_resource($this->device)) {
            fclose($this->device);
        }
    }

}
<?php

// autoload composer
require_once __DIR__ . '/../vendor/autoload.php';

use PhpArduino\Arduino;

// instance class
$ard = new Arduino('COM5', "w+b");

// add command (read new messages)
$ard->sendCommand('command', 'read');

// add command (send message, change with your phone number)
$ard->sendCommand('command', 'send');
$ard->sendCommand('number', '048984632799'); 
$ard->sendCommand('content', 'SMS sent using PHP and Arduino technology');

// add command (wait complete SMS send)
$ard->sendCommand('complete', '');

// get stream (listen port)
$ard->listen();
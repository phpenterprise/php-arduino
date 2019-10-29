<?php

// autoload composer
require_once __DIR__ . '/../vendor/autoload.php';

// instance class
$ard = new Arduino('COM5', "w+b");

// add command (read new messages)
$ard->sendCommand('command', 'read');

// add command (send message, change with your phone number)
$ard->sendCommand('command', 'send');
$ard->sendCommand('number', '047991919191'); 
$ard->sendCommand('content', 'SMS sent using PHP and Arduino technology');

// add command (wait complete SMS send)
$ard->sendCommand('complete', '');

// get stream (listen port)
$ard->listen();
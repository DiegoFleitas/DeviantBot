<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/26/2019
 * Time: 2:48 PM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';

$dt = new DeviantBot\DataLogger();
$dt->logdata('[LASTPOST]');

$CI = new DeviantBot\CommandInterpreter();
$result = $CI->identifyCommand('  KEYWORD BERSERK  ');


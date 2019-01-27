<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/26/2019
 * Time: 2:48 PM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'secrets.php';
require_once 'ImageTransformer.php';
require_once 'ImageFetcher.php';
require_once 'FacebookHelper.php';
require_once 'DataLogger.php';
require_once 'CommandInterpreter.php';

$dt = new DataLogger();
$dt->logdata('[LASTPOST]');

$CI = new CommandInterpreter();
$result = $CI->identifyCommand('  KEYWORD BERSERK  ');


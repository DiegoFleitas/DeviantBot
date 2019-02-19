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

$FB_helper = new DeviantBot\FacebookHelper();
$fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);

$post = $FB_helper->getLastPost($fb);

$raw_comment = $FB_helper->getFirstComment($fb, $post);

//FILTER_SANITIZE_STRING: Strip tags, optionally strip or encode special characters.
//FILTER_FLAG_STRIP_LOW: strips bytes in the input that have a numerical value <32, most notably null bytes and other control characters such as the ASCII bell.
//FILTER_FLAG_STRIP_HIGH: strips bytes in the input that have a numerical value >127. In almost every encoding, those bytes represent non-ASCII characters such as ä, ¿, 堆 etc
$safe_comment = filter_var($comment, FILTER_SANITIZE_STRING,
    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);


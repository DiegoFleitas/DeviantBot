<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/10/2019
 * Time: 10:19 PM
 */

require_once realpath(__DIR__ . '/../..') . '/vendor/autoload.php';

require_once 'secrets.php';
require_once 'ImageTransformer.php';
require_once 'ImageFetcher.php';

# v5 with default access token fallback
$fb = new Facebook\Facebook([
    'app_id' => $_APP_ID,
    'app_secret' => $_APP_SECRET,
    'default_graph_version' => 'v2.10',
]);

$fb->setDefaultAccessToken($_ACCESS_TOKEN_DEBUG);
//    $fb->setDefaultAccessToken($_ACCESS_TOKEN_PAINTBOT);

$FBhelper = new FacebookHelper();
$FBhelper->newPost($fb, $IMAGE_PATH, $IMAGE_LINK, $IMAGE_AUTHOR);
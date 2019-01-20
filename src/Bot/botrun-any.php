<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/11/2019
 * Time: 10:25 PM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'secrets.php';
require_once 'ImageTransformer.php';
require_once 'ImageFetcher.php';
require_once 'FacebookHelper.php';
require_once 'DataLogger.php';

$dt = new DataLogger();
$dt->logdata('[ANY]');

# v5 with default access token fallback
$fb = new Facebook\Facebook([
    'app_id' => $_APP_ID,
    'app_secret' => $_APP_SECRET,
    'default_graph_version' => 'v2.10',
]);
$fb->setDefaultAccessToken($_ACCESS_TOKEN_DEBUG);


$IMAGE_PATH = 'test/transformed_image.jpg';
$tags = array();
$keywords = array();

$ImgFetcher = new ImageFetcher();
$result = $ImgFetcher->FetchSaveTransform('ANY', $IMAGE_PATH, $tags, $keywords);
$IMAGE_LINK = $result['link'];
$IMAGE_AUTHOR = $result['author'];

//$IMAGE_AUTHOR should always be set by now
if(isset($IMAGE_LINK)){

    // Make post with any random image
    $FBhelper = new FacebookHelper();
    $FBhelper->newPost($fb, $IMAGE_PATH, $IMAGE_LINK, $IMAGE_AUTHOR);

} else {

    $message = 'ANY incomplete result, no link';
    $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);

}

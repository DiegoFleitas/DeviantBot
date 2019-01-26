<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/17/2019
 * Time: 12:04 AM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'secrets.php';
require_once 'ImageTransformer.php';
require_once 'ImageFetcher.php';
require_once 'FacebookHelper.php';
require_once 'DataLogger.php';


$dt = new DataLogger();
$dt->logdata('[DAILY]');

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
$result = $ImgFetcher->FetchSaveTransform('DAILY', $IMAGE_PATH, $tags, $keywords);
$MESSAGE = $result['message'];
$COMMENT = $result['comment'];
$COMMENT_PHOTO = $result['comment_photo'];

//$MESSAGE should always be set by now
if(isset($MESSAGE)){

    // Make post with any random image
    $FBhelper = new FacebookHelper();
    $FBhelper->newPost($fb, $IMAGE_PATH, $MESSAGE, $COMMENT, $COMMENT_PHOTO);


} else {

    $message = 'DAILY incomplete result, no link';
    $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);

}
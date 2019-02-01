<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/11/2019
 * Time: 10:25 PM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'resources\secrets.php';
require_once 'Classes\ImageTransformer.php';
require_once 'Classes\ImageFetcher.php';
require_once 'Classes\FacebookHelper.php';
require_once 'Classes\DataLogger.php';

$dt = new DataLogger();
$dt->logdata('[ANY]');

// Make post with any random image
$FB_helper = new FacebookHelper();
$fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);

$IMAGE_PATH = 'debug/test/transformed_image.jpg';

$ImgFetcher = new ImageFetcher();
$result = ImageFetcher::fetchSaveTransform($fb, 'ANY', $IMAGE_PATH);
$SAFETY = $result['safety'];
$POST_TITLE = $result['post_title'];
$POST_COMMENT = $result['post_comment'];
$COMMENT = $result['comment'];
$COMMENT_PHOTO = $result['comment_photo'];

//$MESSAGE should always be set by now
if (isset($POST_TITLE)) {
    // Make post with any random image
    $FB_helper->newPost($fb, $IMAGE_PATH, $POST_TITLE, $POST_COMMENT, $SAFETY, $COMMENT, $COMMENT_PHOTO);
} else {
    $message = 'ANY incomplete result, no link';
    $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
}

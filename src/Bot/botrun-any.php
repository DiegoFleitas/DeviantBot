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

// Make post with any random image
$FBhelper = new FacebookHelper();
$fb = $FBhelper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);

$IMAGE_PATH = 'test/transformed_image.jpg';

$ImgFetcher = new ImageFetcher();
$result = $ImgFetcher->FetchSaveTransform($fb, 'ANY', $IMAGE_PATH);
$POST_TITLE = $result['post_title'];
$POST_COMMENT = $result['post_comment'];
$COMMENT = $result['comment'];
$COMMENT_PHOTO = $result['comment_photo'];

//$MESSAGE should always be set by now
if(isset($POST_TITLE)){

    // Make post with any random image
    $FBhelper->newPost($fb, $IMAGE_PATH, $POST_TITLE, $POST_COMMENT, $COMMENT, $COMMENT_PHOTO);


} else {

    $message = 'ANY incomplete result, no link';
    $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);

}

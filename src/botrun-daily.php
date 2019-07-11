<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/17/2019
 * Time: 12:04 AM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';


try {
    $dt = new DeviantBot\DataLogger();
    $dt->logdata('[DAILY]');

    // Make post with any random image
    $FB_helper = new DeviantBot\FacebookHelper();
    $fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);


    $IMAGE_PATH = __DIR__.'/resources/transformed_image.jpg';

    $ImgFetcher = new DeviantBot\ImageFetcher();
    try {
        $result = $ImgFetcher->fetchSaveTransform($fb, 'DAILY', $IMAGE_PATH);
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
            $message = 'DAILY incomplete result, no link';
            $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        $message = $e->getMessage();
        $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    echo json_encode(debug_backtrace());
}

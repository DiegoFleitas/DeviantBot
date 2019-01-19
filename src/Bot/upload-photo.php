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

try {
    $fb->setDefaultAccessToken($_ACCESS_TOKEN_DEBUG);
//    $fb->setDefaultAccessToken($_ACCESS_TOKEN_PAINTBOT);

    // Remote image
//    $IMAGE_PATH = 'https://i0.wp.com/www.ensenadanoticias.com/wp-content/uploads/2018/11/test.png?resize=800%2C445';
    // Local image
//    $IMAGE_PATH = 'test/new_image.jpg';

    # fileToUpload works with remote and local images
    $data = [
        'source' => $fb->fileToUpload($IMAGE_PATH),
        'message' => 'Beep Boop I found this, but I think it got corrupted along the way.
        
        Original image: 
        '.$IMAGE_LINK.'
        author: '.$IMAGE_AUTHOR,
    ];
    $response = $fb->post('/me/photos', $data);

    // Move image to avoid posting it again
    date_default_timezone_set('America/Montevideo');
    copy($IMAGE_PATH, 'posted/'.date("Y-m-d h_i_sa").'.jpg');

} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
    exit;
}

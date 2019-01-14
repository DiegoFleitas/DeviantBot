<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/10/2019
 * Time: 10:19 PM
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'secrets.php';

# v5 with default access token fallback
$fb = new Facebook\Facebook([
    'app_id' => $_APP_ID,
    'app_secret' => $_APP_SECRET,
    'default_graph_version' => 'v2.10',
]);

try {
    $fb->setDefaultAccessToken($_ACCESS_TOKEN);



    $image = 'https://i0.wp.com/www.ensenadanoticias.com/wp-content/uploads/2018/11/test.png?resize=800%2C445';

    # v5 photo upload example
    $data = [
        'source' => $fb->fileToUpload($image),
        'message' => 'My file!',
    ];

    $response = $fb->post('/me/photos', $data);
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
    exit;
}


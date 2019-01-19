<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/17/2019
 * Time: 12:04 AM
 */

require_once realpath(__DIR__ . '/../..'). '/vendor/autoload.php';
require_once 'ImageFetcher.php';
require_once 'DataLogger.php';

$IMAGE_PATH = 'test/transformed_image.jpg';

$ImgFetcher = new ImageFetcher();
$result = $ImgFetcher->FSTDailyImage($IMAGE_PATH);

if($result){
    $IMAGE_LINK = $result['link'];
    $IMAGE_AUTHOR = $result['author'];
}

include_once('upload-photo.php');
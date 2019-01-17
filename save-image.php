<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/17/2019
 * Time: 12:04 AM
 */

require_once __DIR__ . '/vendor/autoload.php';

require_once 'ImageFetcher.php';

$IMAGE_PATH = 'test/transformed_image.jpg';

$ImgFetcher = new ImageFetcher();
$ImgFetcher->FSTDailyImage($IMAGE_PATH);

include_once ('upload-photo.php');
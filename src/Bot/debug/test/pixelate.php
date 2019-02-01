<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/17/2019
 * Time: 1:26 AM
 */

require_once realpath( __DIR__ . '/../..' ) . '/vendor/autoload.php';

require_once 'ImageFetcher.php';

use Intervention\Image\ImageManagerStatic as Image;


// Pixelate remote image

$IMAGE_LINK = 'https://www.deviantart.com/mirodesign/art/SILKY-TOUCH-705653522';
$ImgFetcher = new ImageFetcher();
$data = $ImgFetcher->getImageData($IMAGE_LINK);

$true_url = $ImgFetcher->directURL($data);
$image_path = 'debug/test/pixelate.jpg';
$ImgFetcher->saveImageLocally($true_url, $image_path);

// configure with favored image driver (gd by default)
Image::configure(array('driver' => 'imagick'));

$img = Image::make($image_path);

$ImgTrans = new ImageTransformer();
$ImgTrans->transformRandomly($img, $image_path, $data->getSafety(), $IMAGE_LINK, 1);
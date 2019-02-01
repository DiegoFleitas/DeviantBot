<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/17/2019
 * Time: 1:26 AM
 */

require_once realpath(__DIR__ . '/../..') . '/vendor/autoload.php';

require_once 'ImageFetcher.php';

use Intervention\Image\ImageManagerStatic as Image;


// Reroll to transform again a local reaction image


// configure with favored image driver (gd by default)
Image::configure(array('driver' => 'imagick'));

$image_path = 'resources/reactions/botcomment_photo.jpg';
$img = Image::make($image_path);

$image_path = 'debug/test/botcomment_photo.jpg';
$ImgTrans = new ImageTransformer();
$ImgTrans->transformRandomly($img, $image_path, "nonadult", 'reaction-reroll', 1);
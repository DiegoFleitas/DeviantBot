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


//$FORCE_FILTER = 1; //FLIP
//$FORCE_FILTER = 2; //ROTATE
//$FORCE_FILTER = 3; //LINES
//$FORCE_FILTER = 4; //BLUR
//$FORCE_FILTER = 9; //COLORIZE
//$FORCE_FILTER = 10; //OPACITY
//$FORCE_FILTER = 11; //WIDEN
//$FORCE_FILTER = 12; //LIMIT COLORS
//$FORCE_FILTER = 13; //SHARPEN
$FORCE_FILTER = 14; //TEXT
//$FORCE_FILTER = 15; //CROP
//$FORCE_FILTER = 16; //FRY
$image_path = 'test/filter_'.$FORCE_FILTER.'.jpg';

// REMOTE
$IMAGE_LINK = 'https://scontent.fmvd3-1.fna.fbcdn.net/v/t1.0-9/50085482_2238280363055084_5496124642005352448_o.jpg?_nc_cat=108&_nc_ht=scontent.fmvd3-1.fna&oh=1d7d19f12b024de8f3280079caba4fd1&oe=5CB40E70';
$IMAGE_LINK = 'https://cdn.discordapp.com/attachments/438590624162250754/539103298339340293/what_the_fuck4.jpg';
$ImgFetcher = new ImageFetcher();
$ImgFetcher->saveImageLocally($IMAGE_LINK, $image_path);

// LOCAL
//$IMAGE_LINK = 'test/image.jpg';

// configure with favored image driver (gd by default)
Image::configure(array('driver' => 'imagick'));

$img = Image::make($image_path);

$ImgTrans = new ImageTransformer();
$ImgTrans->TransformRandomly($img, $image_path, "nonadult", $IMAGE_LINK, 1, $FORCE_FILTER);
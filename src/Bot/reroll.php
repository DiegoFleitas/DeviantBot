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


// Reroll to transform again a remote image

$IMAGE_LINK = 'https://scontent.fmvd3-1.fna.fbcdn.net/v/t1.0-9/50085482_2238280363055084_5496124642005352448_o.jpg?_nc_cat=108&_nc_ht=scontent.fmvd3-1.fna&oh=1d7d19f12b024de8f3280079caba4fd1&oe=5CB40E70';
$ImgFetcher = new ImageFetcher();

$image_path = 'debug/test/comment_photo.jpg';
$success = $ImgFetcher->saveImageLocally($IMAGE_LINK, $image_path);
if ($success) {
    // configure with favored image driver (gd by default)
    Image::configure(array('driver' => 'imagick'));

    /** @var \Intervention\Image\Image $img */
    $img = Image::make($image_path);

    $ImgTrans = new ImageTransformer();
    $ImgTrans->transformRandomly($img, $image_path, "nonadult", $IMAGE_LINK, 1);
}

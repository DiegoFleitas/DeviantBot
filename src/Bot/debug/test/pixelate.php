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
if (empty($true_url)) {
    $message = 'Image link: '.$IMAGE_LINK;
    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
} else {
    $message = 'trying with raw url: '.$IMAGE_LINK;
    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message);
    $true_url = $IMAGE_LINK;
}

$image_path = 'debug/test/pixelate.jpg';
$success = $ImgFetcher->saveImageLocally($true_url, $image_path);
if ($success) {
    // configure with favored image driver (gd by default)
    Image::configure(array('driver' => 'imagick'));

    /** @var \Intervention\Image\Image $img */
    $img = Image::make($image_path);

    $ImgTrans = new ImageTransformer();
    $ImgTrans->transformRandomly($img, $image_path, $data->getSafety(), $IMAGE_LINK, 1);
}

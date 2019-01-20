<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/20/2019
 * Time: 6:28 AM
 */


require_once realpath(__DIR__ . '/../..') . '/vendor/autoload.php';

require_once 'ImageFetcher.php';

use Intervention\Image\ImageManagerStatic as Image;

$ImgFetcher = new ImageFetcher();

$response1 = file_get_contents('test/test-response-popular.xml');
$array1 = $ImgFetcher->parseXMLResponse($response1);

$response2 = file_get_contents('test/test-response-popular2.xml');
$array2 = $ImgFetcher->parseXMLResponse($response2);

$intersection = array_intersect($array1, $array2);
var_dump($intersection);

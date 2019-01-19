<?php
/**
 * Created by PhpStorm.
 * User:
break; Diego
 * Date:
break; 1/15/2019
 * Time:
break; 11:
break;52 PM
 */

require_once 'DataLogger.php';

class ImageTransformer extends DataLogger
{

    function TransformRandomly($img, $path, $safety, $IMAGE_LINK, $n = 1){

        for($n; $n > 0; $n--){
            $do = mt_rand(1, 14);

            if($safety !== 'nonadult'){
                $do = 999;
            }

            $params = array();

            switch($do){
                case 1:
                    $method = 'flip vertically';
                    $img->flip('v');
                    break;
                case 2:
                    $method = 'flip horizontally';
                    $img->flip('h');
                    break;
                case 3:
                    $method = 'blur';
                    $aux = mt_rand(15, 100);
                    array_push($params, $aux);
                    $img->blur($aux);
                    break;
                case 4:
                    $method = 'sharpen';
                    $aux = mt_rand(15, 100);
                    array_push($params, $aux);
                    $img->sharpen($aux);
                    break;
                case 5:
                    $method = 'pixelate';
                    $aux = mt_rand(10, 100);
                    array_push($params, $aux);
                    $img->pixelate($aux);
                    break;
                case 6:
                    $method = 'brightness';
                    $aux = mt_rand(-100, 100);
                    array_push($params, $aux);
                    $img->brightness($aux);
                    break;
                case 7:
                    $method = 'contrast';
                    $aux = mt_rand(-100, 100);
                    array_push($params, $aux);
                    $img->contrast($aux);
                    break;
                case 8:
                    $method = 'greyscale';
                    $img->greyscale();
                    break;
                case 9:
                    $method = 'invert';
                    $img->invert();
                    break;
                case 10:
                    $method = 'colorize';
                    //Generate random color
                    $red = mt_rand(-100, 100);
                    $green = mt_rand(-100, 100);
                    $blue = mt_rand(-100, 100);
                    array_push($params, $red);
                    array_push($params, $green);
                    array_push($params, $blue);
                    $img->colorize($red, $green, $blue);
                    break;
                case 11:
                    $method = 'opacity';
                    $aux = mt_rand(1, 15);
                    array_push($params, $aux);
                    $img->opacity($aux);
                    break;
                case 12:
                    $method = 'widen';
                    $aux = mt_rand(1, 15);
                    array_push($params, $aux);
                    $img->widen($aux);
                    break;
                case 13:
                    $method = 'limitcolors';
                    $aux = mt_rand(1, 255);
                    array_push($params, $aux);
                    $img->limitcolors($aux);
                    break;
                case 13:
                    $method = 'sharpen';
                    $aux = mt_rand(50, 100);
                    array_push($params, $aux);
                    $img->sharpen($aux);
                    break;
                case 15:
                    // FIXME: Font file must be provided to apply text to image.
                    $method = 'text';
                    // Generate random string
                    $length = mt_rand(4, 10);
                    $random_string  = '';
                    $vowels = array("a","e","i","o","u");
                    $consonants = array(
                        'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
                        'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
                    );
                    // Seed it
                    srand((double) microtime() * 1000000);
                    $max = $length/2;
                    for ($i = 1; $i <= $max; $i++)
                    {
                        $random_string .= $consonants[mt_rand(0,19)];
                        $random_string .= $vowels[mt_rand(0,4)];
                    }

                    // use callback to define details
                    $posx = mt_rand(0 ,100);
                    $posy = mt_rand(0 ,100);
                    array_push($params, $posx);
                    array_push($params, $posy);
                    $img->text($random_string, $posx, $posy);
                    break;
                default :
                    $method = 'pixelate';
                    $aux = 20;
                    array_push($params, $aux);
                    $img->pixelate($aux);
                    break;
            }
            $this->logdata('link: '.$IMAGE_LINK.' method: '.$method.' params: '.implode(',',$params));
        }


        // TODO: The optimal size for post (shared) images is 1,200 x 630 pixels.
        $img->save($path);

    }

}
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


class ImageTransformer
{

    function TransformRandomly($img, $path, $safety){

        $do = mt_rand(1, 14);

        if($safety !== 'nonadult'){
            $do = 999;
        }

        switch($do){
            case 1:
                // flip image vertically
                $img->flip('v');
                break;
            case 2:
                // flip image horizontally
                $img->flip('h');
                break;
            case 3:
                $img->blur(mt_rand(15, 100));
                break;
            case 4:
                $img->sharpen(mt_rand(15, 100));
                break;
            case 5:
                $img->pixelate(mt_rand(10, 100));
                break;
            case 6:
                $img->brightness(mt_rand(-100, 100));
                break;
            case 7:
                $img->contrast(mt_rand(-100, 100));
                break;
            case 8:
                $img->greyscale();
                break;
            case 9:
                $img->invert();
                break;
            case 10:
                //Generate random color
                $red = mt_rand(-100, 100);
                $green = mt_rand(-100, 100);
                $blue = mt_rand(-100, 100);
                $img->colorize($red, $green, $blue);
                break;
            case 11:
                $img->opacity(mt_rand(1, 15));
                break;
            case 12:
                $img->widen(mt_rand(1, 15));
                break;
            case 13:
                $img->limitcolors(mt_rand(1, 255));
                break;
            case 14:
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
                $img->text($random_string, $posx, $posy);
                break;
            default :
                $img->pixelate(20);
                break;
        }

        // TODO: The optimal size for post (shared) images is 1,200 x 630 pixels.
        $img->save($path);

    }

}
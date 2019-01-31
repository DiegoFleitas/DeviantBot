<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/15/2019
 * Time: 11:52 PM
 */

require_once 'DataLogger.php';

class ImageTransformer extends DataLogger
{

    /**
     * @param \Intervention\Image\Image $img
     * @param string $path
     * @param string $safety
     * @param string $IMAGE_LINK
     * @param int $n
     * @param bool $forcefilter
     * @return string
     */
    function TransformRandomly($img, $path, $safety, $IMAGE_LINK, $n = 1, $forcefilter = false){

        // n is how many rolls
        for($n; $n > 0; $n--){


            $result = $this->randomTransformation($img, $safety, $forcefilter);

            $this->logdata('link: '.$IMAGE_LINK.' method: '.$result['method'].' params: '.$result['params']);
        }


        // TODO: The optimal size for post (shared) images is 1,200 x 630 pixels.
        $img->save($path);

        if(isset($result['params']) && isset($result['method'])){
            return 'method: '.$result['method'].' params: '.$result['params'];
        } else {
            return '';
        }

    }

    /**
     * @param \Intervention\Image\Image $img
     * @param string $safety
     * @param $forcefilter
     * @return array
     * @desc Transforms the image randomly (Except adult)
     */
    function randomTransformation($img, $safety = 'nonadult', $forcefilter = false){

        $params = array();

        if(!$forcefilter){
            $do = mt_rand(1, 17);

            // if unsafe, pixelate
            if($safety !== 'nonadult'){
                $do = 999;
            }
        } else {
            $do = $forcefilter;
        }

        switch($do){
            case 1:

                // 50% chance of vertical and horizontal
                if( mt_rand(0, 1)){
                    $method = 'flip vertically';
                    $img->flip('v');
                } else{
                    $method = 'flip horizontally';
                    $img->flip('h');
                }
                break;

            case 2:

                $method = 'rotate';
                $aux = mt_rand(0 , 360);
                array_push($params, $aux);
                $img->rotate($aux);
                break;

            case 3:

                $method = 'lines';
                $width = $img->getWidth();
                $height = $img->getHeight();

                for ($i = 0; $i < 4; $i++)
                {
                    $posx1 = mt_rand(0 , $width);
                    $posy1 = mt_rand(0 , $height);
                    $posx2 = mt_rand(0 , $width);
                    $posy2 = mt_rand(0 , $height);

                    $message = 'line'.($i+1);
                    array_push($params, $message);
                    array_push($params, $posx1);
                    array_push($params, $posy1);
                    array_push($params, $posx2);
                    array_push($params, $posy2);

                    // draw a red line with 5 pixel width
                    $img->line($posx1, $posy1, $posx2, $posy2, function ($draw) {
                        /** @var Intervention\Image\Imagick\Shapes\LineShape $draw */
                        $draw->color('#f00');
                        $draw->width(5);
                    });
                }
                break;

            case 4:

                $method = 'blur';
                $aux = mt_rand(10, 20);
                array_push($params, $aux);
                $img->blur($aux);
                break;

            case 5:

                $method = 'brightness';
                if(!$forcefilter){
                    // 75% chance to reroll
                    if(mt_rand(0, 3)){
                        $this->randomTransformation($img);
                    }
                }

                // 100 being the brightest
                // $aux = mt_rand(-100, 100); //too dark, too bright
                $aux = mt_rand(-50, 50);
                array_push($params, $aux);
                $img->brightness($aux);
                break;

            case 6:

                $method = 'greyscale';
                $img->greyscale();
                break;

            case 7:

                $method = 'contrast';
                if(!$forcefilter){
                    // 75% chance to reroll
                    if(mt_rand(0, 3)){
                        $this->randomTransformation($img);
                    }
                }

                // $aux = mt_rand(-100, 100); //too dark, too bright
                $aux = mt_rand(-50, 50);
                array_push($params, $aux);
                $img->contrast($aux);
                break;

            case 8:
                
                $method = 'invert';
                $img->invert();
                break;

            case 9:
                
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

            case 10:

                $method = 'opacity';
                if(!$forcefilter){
                    // 100% chance to reroll since Andi didn't like this filter
                    $this->randomTransformation($img);
                }

                // 100 being the full opacity
                $aux = mt_rand(30, 50);
                array_push($params, $aux);
                $img->opacity($aux);
                break;

            case 11:

                $method = 'widen';
                $actual_width = $img->getWidth();
                // 7% to 10%
                $aux = mt_rand($actual_width * 0.07, $actual_width * 0.1);
                array_push($params, $aux);
                $img->widen($aux);
                break;

            case 12:
                
                $method = 'limitcolors';
                $aux = 5;
                array_push($params, $aux);
                $img->limitcolors($aux);
                break;

            case 13:

                $method = 'sharpen';
                // fry twice, since 100 is the max
                for($i = 0; $i < 2; $i++){
                    $aux = 100;
                    array_push($params, $aux);
                    $img->sharpen($aux);
                }
                break;

            case 14:
                
                $method = 'text';
//                $random_string = $this->randomString();
                $random_string = $this->randomCapital();
                $random_string = strtoupper($random_string);

                $width = $img->getWidth();
                $height = $img->getHeight();

                // upper left
//                $posx = mt_rand(0 , 0);
//                $posy = mt_rand(0 , 0);
                // lower left
//                $posx = mt_rand(0 , 0);
//                $posy = mt_rand($height , $height);
                // upper right
//                $posx = mt_rand($width , $width);
//                $posy = mt_rand(0 , 0);
                // lower right
//                $posx = mt_rand($width , $width);
//                $posy = mt_rand($height , $height);

                // random
                $posx = mt_rand(0 , $width);
                $posy = mt_rand(0 , $height);

                array_push($params, $posx);
                array_push($params, $posy);

                $img->text($random_string, $posx, $posy, function($font) {
                    /** @var Intervention\Image\Imagick\Font $font */
                    $font->file(__DIR__ .'\fonts\lucida');
                    $font->size(mt_rand(24 , 60));
                    // hacker green
                    $font->color('#20c20e');
                    $font->align('center');
                    $font->valign('top');
                    $font->angle(mt_rand(0 , 360));
                });
                break;

            case 15:

                $method = 'crop';
                if(!$forcefilter){
                    //FIXME: 100% chance to reroll since I don't want to think of way to solve
                    // the problem of not picking uninteresting regions right now
                    $this->randomTransformation($img);
                }

                $width = $img->getWidth();
                $height = $img->getHeight();

                $posx1 = mt_rand(0 , $width);
                $posy1 = mt_rand(0 , $height);
                $posx2 = mt_rand(0 , $width);
                $posy2 = mt_rand(0 , $height);

                array_push($params, $posx1);
                array_push($params, $posy1);
                array_push($params, $posy2);
                array_push($params, $posx2);

                // crop image
                $img->crop($posx1, $posy1, $posy2, $posx2);
                break;

            case 16:

                //FIXME this should be a filter()
                //(brightness + invert + sharpen)
                $method = 'demonic fry';

                // 100 being the brightest
                 $aux = mt_rand(-100, -50); //darker
                array_push($params, $aux);
                $img->brightness($aux);

                $img->invert();

                $aux = 100;
                array_push($params, $aux);
                $img->sharpen($aux);
                break;

            case 17: //pixelate
            default :
                $method = 'pixelate';
                $aux = 20;
                array_push($params, $aux);
                $img->pixelate($aux);
                break;

        }

        // no params
        if(count($params) < 1){
            array_push($params, 'none');
        }

        return array(
            'method' => $method,
            'params' => implode(',',$params)
        );

    }

    /**
     * @return string
     */
    function randomString(){
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
        return $random_string;
    }

    /**
     * @return mixed
     */
    function randomCapital(){

        //<editor-fold desc="array with capitals">
        $capitals = array(
            'capital',
            'kabul',
            'tirana',
            'algiers',
            'andorra la vella',
            'luanda',
            'saint john\'s',
            'buenos aires',
            'yerevan',
            'canberra',
            'vienna',
            'baku',
            'nassau',
            'manama',
            'dhaka',
            'bridgetown',
            'minsk',
            'brussels',
            'belmopan',
            'porto-novo',
            'thimphu',
            'sucre',
            'sarajevo',
            'gaborone',
            'brasilia',
            'bandar seri begawan',
            'sofia',
            'ouagadougou',
            'bujumbura',
            'praia',
            'phnom penh',
            'yaounde',
            'ottawa',
            'bangui',
            'n\'djamena',
            'santiago',
            'beijing',
            'bogotÁ',
            'moroni',
            'kinshasa',
            'san jose',
            'yamoussoukro',
            'zagreb',
            'havana',
            'nicosia',
            'prague',
            'copenhagen',
            'djibouti (city)',
            'roseau',
            'santo domingo',
            'quito',
            'cairo',
            'san salvador',
            'malabo',
            'asmara',
            'tallinn',
            'mbabane',
            'addis ababa',
            'palikir',
            'suva',
            'helsinki',
            'paris',
            'libreville',
            'banjul',
            'tbilisi',
            'berlin',
            'accra',
            'athens',
            'saint george\'s',
            'guatemala city',
            'conakry',
            'bissau',
            'georgetown',
            'port-au-prince',
            'tegucigalpa',
            'budapest',
            'reykjavik',
            'new delhi',
            'jakarta',
            'tehran',
            'baghdad',
            'dublin',
            'jerusalem',
            'tel aviv',
            'rome',
            'kingston',
            'tokyo',
            'amman',
            'astana',
            'nairobi',
            'south tarawa',
            'pristina',
            'kuwait city',
            'bishkek',
            'vientiane',
            'riga',
            'beirut',
            'maseru',
            'monrovia',
            'tripoli',
            'vaduz',
            'vilnius',
            'luxembourg',
            'skopje',
            'antananarivo',
            'lilongwe',
            'kuala lumpur',
            'male',
            'bamako',
            'valletta',
            'majuro',
            'nouakchott',
            'port louis',
            'mexico city',
            'chisinau',
            'monaco',
            'ulaanbaatar',
            'podgorica',
            'rabat',
            'maputo',
            'nay pyi taw',
            'windhoek',
            'yaren district',
            'kathmandu',
            'amsterdam',
            'wellington',
            'managua',
            'niamey',
            'abuja',
            'pyongyang',
            'oslo',
            'muscat',
            'islamabad',
            'ngerulmud',
            'ramallah',
            'panama city',
            'port moresby',
            'asunciÓn',
            'lima',
            'manila',
            'warsaw',
            'lisbon',
            'doha',
            'brazzaville',
            'bucharest',
            'moscow',
            'kigali',
            'basseterre',
            'castries',
            'kingstown',
            'apia',
            'san marino',
            'sÃo tomÉ',
            'riyadh',
            'dakar',
            'belgrade',
            'victoria',
            'freetown',
            'singapore',
            'bratislava',
            'ljubljana',
            'honiara',
            'mogadishu',
            'bloemfontein',
            'cape town',
            'pretoria',
            'seoul',
            'juba',
            'madrid',
            'colombo',
            'sri jayawardenepura kotte',
            'khartoum',
            'paramaribo',
            'stockholm',
            'bern',
            'damascus',
            'dushanbe',
            'dodoma',
            'bangkok',
            'dili',
            'lomÉ',
            'nukuʻalofa',
            'port of spain',
            'tunis',
            'ankara',
            'ashgabat',
            'funafuti',
            'kampala',
            'kiev',
            'abu dhabi',
            'london',
            'washington, d.c.',
            'montevideo',
            'tashkent',
            'port vila',
            'vatican city',
            'caracas',
            'hanoi',
            'sana\'a',
            'lusaka',
            'harare',
        );
        //</editor-fold>

        $random_index = mt_rand(0, count($capitals) - 1);
        return $capitals[$random_index];

    }

}
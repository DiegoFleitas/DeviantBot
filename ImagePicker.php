<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:46 PM
 */

class ImagePicker
{

    function pickTerribleImage($json_data){

        $isTerribleImage = $isExtraTerribleImage = false;

        // No zucc pls
        if($json_data->safety !== 'nonadult'){
            return false;
        }

        $terrible_users = array(
            'plumpchu',
            'fattypreggo',
            'skeletonnekogems12'
        );

        $isTerribleAuthor = in_array($json_data->author_name, $terrible_users);

        if(isset($json_data->tags)){

            $extra_terrible_tags = array(
                '#oc',
                '#dab',
                '#fatoc',
                '#webcomicoc',
                '#undertaleoc',
                '#sonicoc',
                '#sonicfancharacter',
                '#fan_character',
                '#ocxcanon',
                '#sansxoc',
                '#musclegirl',
                '#tickled',
                '#infalted'
            );
            $terrible_tags = array(

                '#ocxcanonshipping',

                '#barefeet',
                '#feet',
                '#foot',
                '#longlegs',
                '#sexy',
                '#cutekawaii',
                '#footfetish',
                '#tickles',
                '#tickling',

                '#bbw',
                '#belly',
                '#cake',
                '#chubby',
                '#chubbygirl',
                '#chubbyobese',
                '#curvy',
                '#expansion',
                '#fat',
                '#fatass',
                '#fatgirl',
                '#fatlegs',
                '#feed',
                '#feedee',
                '#feeder',
                '#feeding',
                '#forcefeeding',
                '#gainer',
                '#jiggle',
                '#lard',
                '#lardass',
                '#morbidlyobese',
                '#obese',
                '#obesity',
                '#plump',
                '#ssbbw',
                '#stuffer',
                '#stuffing',
                '#thighs',
                '#tubby',
                '#weightgain',
                '#wg',
                '#plumpgirl',
                '#fatsexy',
                '#stuffedbelly',
                '#thickthighs',
                '#thickwomen',
                '#chubbybelly',
                '#weightgainfat',
                '#fatbellygirl',
                '#fatcosplay',
                '#thick',
                '#fatbelliedwoman',
            );

            $image_tags = explode(',', $json_data->tags);
            $isTerribleImage = array_intersect($image_tags, $terrible_tags);
            $isExtraTerribleImage = array_intersect($image_tags, $extra_terrible_tags);
        }

        if($isTerribleAuthor || !empty($isTerribleImage) || !empty($isExtraTerribleImage)){
            return true;
        }
        return false;

    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/11/2019
 * Time: 10:25 PM
 */

//  Media RSS
//    Browse Newest Deviations (including subcategories)
//https://backend.deviantart.com/rss.xml?type=deviation&q=by:spyed sort:time meta:all
//http://backend.deviantart.com/rss.xml?q=in:photography+sort:time
//    Search Deviations
//https://backend.deviantart.com/rss.xml?type=deviation&q=boost:popular in:digitalart/drawings frogs
//http://backend.deviantart.com/rss.xml?q=boost:popular+fish
//    User Gallery
//http://backend.deviantart.com/rss.xml?q=gallery:mudimba
//    User Favorites
//http://backend.deviantart.com/rss.xml?q=favby:mudimba
//    Daily Deviations
//http://backend.deviantart.com/rss.xml?q=special:dd

//  REGULAR RSS
//    User Journals
//http://backend.deviantart.com/rss.xml?q=by:lolly&type=journal
//    Browse News
//http://backend.deviantart.com/rss.xml?q=sort:pie&type=news
//    Browse Forums
//http://backend.deviantart.com/rss.xml?q=in:devart/general&type=forums


//    Embed DeviantArt media
//https://backend.deviantart.com/oembed?url=http://fav.me/d2enxz7
//    Will return this JSON response:
//{
//  "version": "1.0",
//  "type": "photo",
//  "title": "Cope",
//  "url": "https://fc04.deviantart.net/fs50/f/2009/336/4/7/Cope_by_pachunka.jpg",
//  "author_name": "pachunka",
//  "author_url": "https://pachunka.deviantart.com",
//  "provider_name": "DeviantArt",
//  "provider_url": "https://www.deviantart.com",
//  "thumbnail_url": "https://th03.deviantart.net/fs50/300W/f/2009/336/4/7/Cope_by_pachunka.jpg",
//  "thumbnail_width": 300,
//  "thumbnail_height": 450,
//  "width": 448,
//  "height": 672
//}

//    &offset=0

//    https://www.deviantart.com/whats-hot/?q=#OC+#BERSERK
//    https://www.deviantart.com/undiscovered/?q=#OC+#BERSERK
//    https://www.deviantart.com/popular-all-time/?section=&global=1&q=#OC #BERSERK
//    https://www.deviantart.com/newest/?q=BERSERK => https://backend.deviantart.com/rss.xml?&q=berserk sort:time
//    https://www.deviantart.com/newest/?q=berserk+oc => https://backend.deviantart.com/rss.xml?&q=berserk oc sort:time
//    https://www.deviantart.com/newest/?q=BERSERK+#oc => https://backend.deviantart.com/rss.xml?&q=berserk sort:time tag:oc
//    https://www.deviantart.com/newest/?q=#OC+#BERSERK => https://backend.deviantart.com/rss.xml?&q=sort:time tag:oc tag:berserk

$url_web = 'https://www.deviantart.com/newest';

/**
 * @param array $tags
 * @param array $keywords
 * @param int $newest
 * @return bool|string
 */
function buildRSSURL($tags = [], $keywords = [], $newest = 0){
    $url_rss = 'http://backend.deviantart.com/rss.xml?&q=';

    if(empty($tags) && empty($keywords)){
        echo 'buildRSSURL empty url';
        return false;
    }

    $params = '';
    foreach($keywords as $keyword){
        $params .= $keyword.' ';
    }

    foreach($tags as $tag){
        $params .= 'tag:'.$tag.' ';
    }

    if($newest){
        $params .= 'sort:time ';
    }

    $url_rss .= rawurlencode($params).'&=';

    return $url_rss;
}

/**
 * @desc GET request to DeviantArt servers
 * @param $url
 * @param string $type
 * @return bool|string
 */
function getRawDeviantArtData($url, $media = 'JSON'){

    $curl = curl_init();

    //    Internet Explorer 6 on Windows XP SP2
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, "");
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($curl, CURLOPT_POSTFIELDS, "");
    curl_setopt($curl, CURLOPT_USERAGENT, $agent);
    curl_setopt($curl, CURLOPT_HTTPHEADER,  array(
        "Postman-Token: 8fb92482-5540-4a75-ad8e-0825f73074b8",
        "cache-control: no-cache"
    ));

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo $media." cURL Error #:" . $err;
        return false;
    } else {
        if($httpcode != '200'){
            echo $media." Http code error #:" . $httpcode;
            return false;
        }
        return $response;
    }
}

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
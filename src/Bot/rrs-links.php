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

include_once('DeviantImage.php');

$ALL_IMAGES = [];

// default tags
$tags = array(
    'berserk',
    'oc'
);
$keywords = array();

$ImgFetch = new ImageFetcher();
$links = $ImgFetch->getImagelinksFromRSS($tags, $keywords, 1);
foreach($links as $link){
    $ALL_IMAGES[$link] = $ImgFetch->getImageData($link);
}

print_r($ALL_IMAGES);

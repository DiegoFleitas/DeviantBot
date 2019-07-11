<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/11/2019
 * Time: 10:25 PM
 */

//  Media RSS
//    Browse Newest Deviations (including subcategories)
//https://backend.deviantart.com/rss.xml?type=deviation&q=sort:time meta:all
//https://backend.deviantart.com/rss.xml?type=deviation&q=by:spyed
//http://backend.deviantart.com/rss.xml?q=in:photography
//    Search Deviations
//https://backend.deviantart.com/rss.xml?type=deviation&q=boost:popular in:digitalart/drawings frogs
//http://backend.deviantart.com/rss.xml?q=boost:popular+fish
//http://backend.deviantart.com/rss.xml?q=boost:popular max_age:24h
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

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';


// TODO: A) Being able to comment a DeviantArt link and get it transformed randomly in the comments.
// TODO: C) Being able to transform randomly any image uploaded as comment

try {

    $dt = new DeviantBot\DataLogger();
    $dt->logdata('[POPULAR]');

    // Make post with any random image
    $FB_helper = new DeviantBot\FacebookHelper();
    $fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);


    $IMAGE_PATH = 'resources/transformed_image.jpg';

    $ImgFetcher = new DeviantBot\ImageFetcher();
    try {
        $result = $ImgFetcher->fetchSaveTransform($fb, 'POPULAR', $IMAGE_PATH);
        $SAFETY = $result['safety'];
        $POST_TITLE = $result['post_title'];
        $POST_COMMENT = $result['post_comment'];
        $COMMENT = $result['comment'];
        $COMMENT_PHOTO = $result['comment_photo'];

        //$MESSAGE should always be set by now
        if (isset($POST_TITLE)) {
            // Make post with any random image
            $FB_helper->newPost($fb, $IMAGE_PATH, $POST_TITLE, $POST_COMMENT, $SAFETY, $COMMENT, $COMMENT_PHOTO);
        } else {
            $message = 'POPULAR incomplete result, no link';
            $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        $message = $e->getMessage();
        $dt->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    echo json_encode(debug_backtrace());
}

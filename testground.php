<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/13/2019
 * Time: 1:45 PM
 */

require_once 'deviant2.php';


/**
 * @desc Test RSS flow from WEB
 */
function TEST_getRSSData(){
    $tags = array(
        'berserk',
        'oc'
    );

    $CURLOPT_URL = buildRSSURL( $tags, [], 1  );

    if($CURLOPT_URL){
        $response = getRawDeviantArtData($CURLOPT_URL, 'RSS');
        if($response){
            //Process XML
            try {
                 $links = TEST_parseXMLResponse($response);
                 if(!empty($links)){
                     return $links;
                 } else {
                     return false;
                 }
            } catch (Exception $e){
                echo $e;
            }
        }
    } else {
        echo 'Invalid URL';
    }

}


/**
 * @desc Test parse and access SimpleXMLElement from local XML
 * @param null $response
 * @return mixed
 */
function TEST_parseXMLResponse($response = NULL){
    if($response == NULL){
        $response = file_get_contents('test-response.xml');
    }
    $xml = new SimpleXMLElement($response);
    $links_array = array();
    foreach($xml->xpath('channel/item') as $item){
        // Asumes every item has link
        array_push($links_array,  (string)$item->link) ;
    }
    return $links_array;
}

/**
 * @desc Test JSON flow from WEB
 */
function TEST_getJsonData($link = NULL){
    if(link == NULL){
        $image_url = "https://zancan.deviantart.com/art/Home-and-the-Fairies-21248847";
    } else{
        $image_url = $link;
    }

    $CURLOPT_URL = "https://backend.deviantart.com/oembed?url=".rawurlencode($image_url);

    $response = getRawDeviantArtData($CURLOPT_URL, "JSON");
    if($response){
        return TEST_parseJsonData($response);
    }
}

/**
 * @desc Test parse image based on JSON data from local JSON
 * @param null $response
 */
function TEST_parseJsonData($response = NULL){
    if($response == NULL){
        $response = file_get_contents('test-response.json');
    }
    if($response){
        return json_decode($response);
    }
}

/**
 * @desc Test JSON and RSS flow
 */
function TEST_getRawDeviantArtData(){

    $links = TEST_getRSSData();
    $json_array = array();
    foreach($links as $link){
        $json_array[$link] = TEST_getJsonData($link);
    }
    print_r($json_array);

}

TEST_getRawDeviantArtData();

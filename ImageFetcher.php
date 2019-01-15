<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:45 PM
 */

class ImageFetcher
{

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

    function getImageData($link){

        $CURLOPT_URL = "https://backend.deviantart.com/oembed?url=".rawurlencode($link);

        $response = $this->getRawDeviantArtData($CURLOPT_URL, "JSON");
        if($response){
            return new DeviantImage($response);
        }
    }

    function getImagelinksFromRSS($tags){

        $CURLOPT_URL = $this->buildRSSURL( $tags, [], 1  );

        if($CURLOPT_URL){
            $response = $this->getRawDeviantArtData($CURLOPT_URL, 'RSS');
            if($response){
                //Process XML
                try {
                    $links = $this->parseXMLResponse($response);
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
    function parseXMLResponse($response){
        $xml = new SimpleXMLElement($response);
        $links_array = array();
        foreach($xml->xpath('channel/item') as $item){
            // Asumes every item has link
            array_push($links_array,  (string)$item->link) ;
        }
        return $links_array;
    }


}
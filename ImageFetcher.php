<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:45 PM
 */


require_once 'DeviantImage.php';
require_once 'ImageTransformer.php';
require_once 'Logger.php';

use Intervention\Image\ImageManagerStatic as Image;

class ImageFetcher extends DataLogger
{

    /**
     * @param array $tags
     * @param array $keywords
     * @param int $newest
     * @return bool|string
     */
    function buildRSSURL($tags = [], $keywords = [], $newest = 0, $dailydeviations = 0){
        $url_rss = 'http://backend.deviantart.com/rss.xml?&q=';

        if(empty($tags) && empty($keywords) && !$dailydeviations){
            logdata('['.__METHOD__.' ERROR] empty url', 1);
        }

        if($dailydeviations){
            $url_rss .= 'special:dd';
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

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = $media." cURL Error #:" . $err.'  url: '.$url;
            $this->logdata('['.__METHOD__.' ERROR] '.__DIR__.':'.__LINE__.' '.$message, 1);
        } else {
            if($httpcode != '200'){
                $message =  $media." Http code error #:" . $httpcode.'  url: '.$url;;
                $this->logdata('['.__METHOD__.' ERROR] '.__DIR__.':'.__LINE__.' '.$message, 1);
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

    /**
     * @param $tags
     * @param int $type 1 for newest (tagged keyword) 2 for daily deviations (tagged keyword)
     * @return bool|mixed
     */
    function getImagelinksFromRSS($tags, $keywords, $type = 1){

        // Newest tagged
        if($type == 1){
            $CURLOPT_URL = $this->buildRSSURL( $tags, $keywords, 1  );
        } elseif($type == 2){
            // DailyDeviations
            $CURLOPT_URL = $this->buildRSSURL( $tags, $keywords, 0, 1  );
        }

        $response = $this->getRawDeviantArtData($CURLOPT_URL, 'RSS');
        if($response){
            //Process XML
            try {

                $links = $this->parseXMLResponse($response);

                if(!empty($links)){
                    return $links;
                }

            } catch (Exception $e){
                echo $e;
            }
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


    function randomDaily(){

        // TODO: check if tags and keywords are enabled on Daily
        $tags = array();
        $keywords = array();

        $ImgFetch = new ImageFetcher();
        $links = $ImgFetch->getImagelinksFromRSS($tags, $keywords, 2);

        if(!empty($links)){
            $random_index = mt_rand(0, count($links) - 1);
            return $links[$random_index];
        }

        $this->logdata('['.__METHOD__.' ERROR] '.__DIR__.':'.__LINE__, 1);

    }

    function saveImageLocally($url, $path){

        $curl = curl_init($url);
        $fp = fopen($path, 'wb');

        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);
        fclose($fp);

        if ($err) {
            $message = "SaveImage cURL Error #:" . $err.' url:'.$url;
            $this->logdata('['.__METHOD__.' ERROR] '.__DIR__.':'.__LINE__.' '.$message, 1);
        } else {
            if($httpcode != '200'){
                $message =  "SaveImage Http code error #:" . $httpcode.' url:'.$url;
                $this->logdata('['.__METHOD__.' ERROR] '.__DIR__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }

    }

    function directURL($DeviantImage){

        /** @var $DeviantImage DeviantImage */
        $h = $DeviantImage->getHeight();
        $w = $DeviantImage->getWidth();
        $url = $DeviantImage->getThumbnailUrl();

        $aux1 = 'fill/w_'. $w . ',h_'. $h;
        $url = str_replace('fit/w_300,h_900', $aux1, $url);
        $url = str_replace('300w', 'fullview', $url);

        return $url;

    }


    /**
     * @desc FETCH SAVE TRANSFORM Daily Image
     */
    function FSTDailyImage($IMAGE_PATH){

        $ImgFetcher = new ImageFetcher();
        $IMAGE_LINK = $ImgFetcher->randomDaily();

        try{

            if(!empty($IMAGE_LINK)){
                $data = $ImgFetcher->getImageData($IMAGE_LINK);

                if($data){
                    $IMAGE_AUTHOR = $data->getAuthorName();
                }

                $true_url = $ImgFetcher->directURL($data);

                $IMAGE_PATH_NEW = 'test/new_image.jpg';

                $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);

                // configure with favored image driver (gd by default)
                Image::configure(array('driver' => 'imagick'));

                $img = Image::make($IMAGE_PATH_NEW);

                $ImgTrans = new ImageTransformer();
                $ImgTrans->TransformRandomly($img, $IMAGE_PATH, $data->getSafety(), $IMAGE_LINK, 2);
            } else {
                $this->logdata('['.__METHOD__.' ERROR] '.__DIR__.':'.__LINE__, 1);
            }

            return array(
                'link' => $IMAGE_LINK,
                'author' => $IMAGE_AUTHOR
            );

        } catch (Exception $e){
            echo $e->getMessage();
        }

    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:45 PM
 */


require_once 'DeviantImage.php';
require_once 'ImageTransformer.php';
require_once 'DataLogger.php';
require_once 'ImageClassifier.php';
require_once 'CommandInterpreter.php';

use Intervention\Image\ImageManagerStatic as Image;

class ImageFetcher extends DataLogger
{


    /**
     * @param string $dailydeviations
     * @param string $popular
     * @param array $tags
     * @param array $keywords
     * @return string
     */
    function buildRSSURL($dailydeviations, $popular, $tags, $keywords){

        $url_rss = 'http://backend.deviantart.com/rss.xml?&q=';

        if($dailydeviations){
            // Daily deviations
            $url_rss .= 'special:dd'.rawurlencode(' ');
        } elseif($popular) {
            // Popular from last 24 hours
            $url_rss .= 'boost:popular'.rawurlencode(' max_age:24h ');
        } else {
            // Any
            $url_rss .= 'meta:all'.rawurlencode(' ');
        }

        $params = '';
        //TODO add searching by title
        foreach($keywords as $keyword){
            $params .= $keyword.' ';
        }

        foreach($tags as $tag){
            $params .= 'tag:'.$tag.' ';
        }

        // Exclude literature category since most are just text
        $lit = '-in:literature ';
        $url_rss .= rawurlencode($lit);


        if(!$popular){
            $params .= 'sort:time ';
        }

        $url_rss .= rawurlencode($params).'&=';

        // logging
        $message = 'fetching [' . $url_rss . ']';
        $this->logdata($message);

        return $url_rss;
    }


    /**
     * @desc GET request to DeviantArt servers
     * @param string $url
     * @param string $media
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
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if($httpcode != '200'){
                $message =  $media." Http code error #:" . $httpcode.'  url: '.$url;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }
    }

    /**
     * @param string $link
     * @return DeviantImage
     */
    function getImageData($link){

        $CURLOPT_URL = "https://backend.deviantart.com/oembed?url=".rawurlencode($link);

        $response = $this->getRawDeviantArtData($CURLOPT_URL, "JSON");
        if($response){
            return new DeviantImage($response);
        }
    }

    /**
     * @param string $type
     * @param array $tags
     * @param array $keywords
     * @return mixed
     */
    function getImagelinksFromRSS($type, $tags, $keywords){

        if($type == 'DAILY'){
            // DailyDeviations
            //http://backend.deviantart.com/rss.xml?q=special:dd sort:time
            $CURLOPT_URL = $this->buildRSSURL( true, false, $tags, $keywords);
        } elseif($type == 'POPULAR'){
            // Newest popular
            //http://backend.deviantart.com/rss.xml?q=boost:popular max_age:24h sort:time
            $CURLOPT_URL = $this->buildRSSURL( false, true, $tags, $keywords);
        } elseif($type == 'ANY'){
            // Newest Any
            //http://backend.deviantart.com/rss.xml?q=meta:all sort:time
            $CURLOPT_URL = $this->buildRSSURL( false, false, $tags, $keywords);
        }

        $response = $this->getRawDeviantArtData($CURLOPT_URL, 'RSS');
        if($response){
            //Process XML
            try {

                $links = $this->parseXMLResponse($response);

                $this->logxml($type, $response);

                if(!empty($links)){
                    return $links;
                }

            } catch (Exception $e){
                $message = $e->getMessage();
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
        }


    }

    /**
     * @desc parse and access SimpleXMLElement from local XML
     * @param string $response
     * @return array
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


    /**
     * @param string $TYPE
     * @param array $tags
     * @param array $keywords
     * @return mixed
     */
    function getRandom($TYPE, $tags, $keywords){

        $ImgFetch = new ImageFetcher();
        $links = $ImgFetch->getImagelinksFromRSS($TYPE, $tags, $keywords);

        if(!empty($links)){
            $random_index = mt_rand(0, count($links) - 1);
            return $links[$random_index];
        }

        $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__, 1);

    }

    /**
     * @param string $url
     * @param string $path
     * @return bool|string
     */
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
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if($httpcode != '200'){
                $message =  "SaveImage Http code error #:" . $httpcode.' url:'.$url;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }

    }

    /**
     * @param DeviantImage $DeviantImage
     * @return mixed
     */
    function directURL($DeviantImage){

        $h = $DeviantImage->getHeight();
        $w = $DeviantImage->getWidth();

        $url = $DeviantImage->getThumbnailUrl();

        $aux1 = 'fill/w_'. $w . ',h_'. $h;
        $url = str_replace('fit/w_300,h_900', $aux1, $url);
        $url = str_replace('300w', 'fullview', $url);

        return $url;

    }


    /**
     * @param $fb \Facebook\Facebook
     * @param string $TYPE
     * @param string $IMAGE_PATH
     * @return array
     */
    function FetchSaveTransform($fb, $TYPE, $IMAGE_PATH){


        $FBhelper = new FacebookHelper();
        $comment = $FBhelper->firstCommandFromLastPost($fb);

        $CI = new CommandInterpreter();
        $result = $CI->identifyCommand($comment);

        $tags = array();
        $keywords = array();
        // Use commands given in comment
        if($result){
            if($result['command'] == 'keyword'){
                $keywords = $result['params'];
            } elseif($result['command'] == 'tag'){
                $tags = $result['params'];
            }
        }

        $ImgFetcher = new ImageFetcher();
        $IMAGE_LINK = $ImgFetcher->getRandom($TYPE, $tags, $keywords);


        try{

            if(!empty($IMAGE_LINK)){

                $data = $ImgFetcher->getImageData($IMAGE_LINK);

                if(isset($data)){
                    // If not set returns Uknown
                    $IMAGE_AUTHOR = $data->getAuthorName();
                }

                $true_url = $ImgFetcher->directURL($data);

                if(empty($true_url)){
                    $message = 'Image link: '.$IMAGE_LINK;
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }

                $IMAGE_PATH_NEW = 'test/original-image.jpg';

                $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);

                // configure with favored image driver (gd by default)
                Image::configure(array('driver' => 'imagick'));

                $img = Image::make($IMAGE_PATH_NEW);

                $ImgTrans = new ImageTransformer();

                // Transform only once
                $TRANSFORM_TIMES = 1;
                $method_params = $ImgTrans->TransformRandomly($img, $IMAGE_PATH, $data->getSafety(), $IMAGE_LINK, $TRANSFORM_TIMES);

            } else {
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__, 1);
            }

            $ImageClassify = new ImageClassifier();

            return array(
                'message' => $ImageClassify->getPostMessage($IMAGE_LINK, $IMAGE_AUTHOR, $method_params),
                'comment' => $ImageClassify->getComment($data),
                'comment_photo' => $ImageClassify->getPhoto($data)
            );

        } catch (Exception $e){
            $message =  $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }

    }

}
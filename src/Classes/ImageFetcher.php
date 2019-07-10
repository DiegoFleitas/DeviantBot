<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:45 PM
 */

namespace DeviantBot;

use Intervention\Image\ImageManagerStatic as Image;

require_once('DeviantImage.php');
require_once('ImageClassifier.php');
require_once('DataLogger.php');

class ImageFetcher extends DataLogger
{


    /**
     * @param string $dailydeviations
     * @param string $popular
     * @param array $tags
     * @param array $keywords
     * @return string
     */
    public function buildRSSURL($dailydeviations, $popular, $tags, $keywords)
    {

        $url_rss = 'https://backend.deviantart.com/rss.xml?&q=';

        if ($dailydeviations) {
            // Daily deviations
            $url_rss .= 'special:dd'.rawurlencode(' ');
        } elseif ($popular) {
            // Popular from last 24 hours
            $url_rss .= 'boost:popular'.rawurlencode(' max_age:24h ');
        } else {
            // Any
            $url_rss .= 'meta:all'.rawurlencode(' ');
        }

        $params = '';
        //TODO add searching by title
        foreach ($keywords as $keyword) {
            $params .= $keyword.' ';
        }

        foreach ($tags as $tag) {
            $params .= 'tag:'.$tag.' ';
        }

        // Exclude literature category since most are just text
        // Not compatible with meta:all tag
        if ($dailydeviations || $popular) {
            $lit = '-in:literature ';
            $url_rss .= rawurlencode($lit);
        }


        if (!$popular) {
            $params .= 'sort:time ';
        }

        $url_rss .= rawurlencode($params).'&=,';

        // logging
        $message = 'fetching [' . $url_rss . ']';
        $this->logdata($message);

        return $url_rss;
    }


    /**
     * @desc GET request to DeviantArt servers
     * @param string $url
     * @param string $media
     * @return string
     */
    public function getRawDeviantArtData($url, $media = 'JSON')
    {

        $curl = curl_init();

//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_ENCODING, "");
//        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
//        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
//        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
//        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
//        curl_setopt($curl, CURLOPT_POSTFIELDS, "");

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: en-US,en;q=0.5",
                "Cache-Control: max-age=0",
                "Connection: keep-alive",
                "Upgrade-Insecure-Requests: 1",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0",
                "cache-control: no-cache"
            ),
        ));


        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = $media.' cURL Error #:' . $err.'  request headers: '.$headers.'  url: '.$url.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  $media.' Http code error #:' . $httpcode.'  request headers: '.$headers.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }
        return '';
    }

    /**
     * @param string $link
     * @return DeviantImage
     */
    public function getImageData($link)
    {

        $CURLOPT_URL = "https://backend.deviantart.com/oembed?url=".rawurlencode($link);

        $response = $this->getRawDeviantArtData($CURLOPT_URL, "JSON");
        if (!empty($response)) {
            return new DeviantImage($response, $CURLOPT_URL);
        } else {
            $message = 'no response from curl '.$CURLOPT_URL;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }

    /**
     * @param string $type
     * @param array $tags
     * @param array $keywords
     * @return array
     */
    public function getImagelinksFromRSS($type, $tags, $keywords)
    {

        if ($type == 'DAILY') {
            // DailyDeviations
            //http://backend.deviantart.com/rss.xml?q=special:dd sort:time
            $CURLOPT_URL = $this->buildRSSURL(true, false, $tags, $keywords);
        } elseif ($type == 'POPULAR') {
            // Newest popular
            //http://backend.deviantart.com/rss.xml?q=boost:popular max_age:24h sort:time
            //http://backend.deviantart.com/rss.xml?&q=boost:popular max_age:24h -in:literature
            $CURLOPT_URL = $this->buildRSSURL(false, true, $tags, $keywords);
        } elseif ($type == 'ANY') {
            // Newest Any
            //http://backend.deviantart.com/rss.xml?q=meta:all sort:time
            $CURLOPT_URL = $this->buildRSSURL(false, false, $tags, $keywords);
        }

        if (!empty($CURLOPT_URL)) {
            $response = $this->getRawDeviantArtData($CURLOPT_URL, 'RSS');
            if (!empty($response)) {
                //Process XML
                try {
                    $this->logxml($type, $response);

                    /** @var  $links array */
                    $links = $this->parseXMLResponse($response);

                    if (!empty($links)) {
                        return $links;
                    }
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }
            }
        } else {
            $message = '$CURLOPT_URL empty';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        return [];
    }

    /**
     * @desc parse and access SimpleXMLElement from local XML
     * @param string $response
     * @return array
     */
    public function parseXMLResponse($response)
    {
        $xml = new \SimpleXMLElement($response);
        $links_array = array();
        /** @var $item SimpleXMLElement */
        foreach ($xml->xpath('channel/item') as $item) {
            if (!empty($item->link)) {
                array_push($links_array, (string)$item->link) ;
            } else {
                if (!empty($item)) {
                    $title = $item->title;
                    $message = $title.' has no links';
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message);
                } else {
                    $message = 'weird xml';
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }
            }
        }
        return $links_array;
    }


    /**
     * @param string $TYPE
     * @param array $tags
     * @param array $keywords
     * @return string
     */
    public function getRandom($TYPE, $tags, $keywords)
    {

        $ImgFetch = new ImageFetcher();
        $links = $ImgFetch->getImagelinksFromRSS($TYPE, $tags, $keywords);

        if (!empty($links)) {
            $random_index = mt_rand(0, count($links) - 1);
            return $links[$random_index];
        } else {
            $message = 'no links found, retrying with no commands';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message);
            return '';
        }
    }

    /**
     * @param string $url
     * @param string $path
     * @return bool
     */
    public function saveImageLocally($url, $path)
    {

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
            $message = 'SaveImage cURL Error #:' . $err.' url:'.$url.' response:'.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  'SaveImage Http code error #:' . $httpcode.' url:'.$url.' response:'.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return true;
        }
        return false;
    }

    /**
     * @param DeviantImage $DeviantImage
     * @return string
     */
    public function directURL($DeviantImage)
    {

        $h = $DeviantImage->getHeight();
        $w = $DeviantImage->getWidth();

        $url = $DeviantImage->getThumbnailUrl();

        if (!empty($url)) {
            $aux1 = 'fill/w_'. $w . ',h_'. $h;
            $url = str_replace('fit/w_300,h_900', $aux1, $url);
            $url = str_replace('300w', 'fullview', $url);
            return $url;
        } else {
            return '';
        }
    }


    /**
     * @param $fb Facebook\Facebook
     * @param string $TYPE
     * @param string $IMAGE_PATH
     * @return array
     */
    public function fetchSaveTransform($fb, $TYPE, $IMAGE_PATH)
    {

        $FB_helper = new FacebookHelper();
        $comment_info = $FB_helper->firstCommandFromLastPost($fb);

        $tags = array();
        $keywords = array();

        $inform = '';
        $method_params = '';

        if (!empty($comment_info)) {
            $CI = new CommandInterpreter();
            $result = $CI->identifyCommand($comment_info['text']);
            // Use commands given in comment
            if (!empty($result)) {
                // invalid
                if ($result['output']) {
                    $inform = $result['output'];
                } else {
                    if ($result['command'] == 'keyword') {
                        $keywords = $result['params'];
                    } elseif ($result['command'] == 'tag') {
                        $tags = $result['params'];
                    }
                }
            }
        } else {
            $message =  'empty comment';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message);
        }

        $ImgFetcher = new ImageFetcher();
        $IMAGE_LINK = $ImgFetcher->getRandom($TYPE, $tags, $keywords);
//        $IMAGE_LINK = 'https://disharmonica.deviantart.com/art/Cosplay-Saber-Nero-787204697';
        // search failed
        if (empty($IMAGE_LINK)) {
            // if failed search had tag or keyword
            if (isset($tags) || isset($keywords)) {
                // will search randomly on its own
                $inform = 'Valid command, but found no results.';
                $IMAGE_LINK = $ImgFetcher->getRandom($TYPE, [], []);
            }
        }

        try {
            if (!empty($IMAGE_LINK)) {

                /** @var DeviantImage $data */
                $data = $ImgFetcher->getImageData($IMAGE_LINK);

                if (isset($data)) {
                    // If not set returns Uknown
                    $IMAGE_AUTHOR = $data->getAuthorName();
                }

                $true_url = $ImgFetcher->directURL($data);

                if (empty($true_url)) {
                    $message = 'Image link: '.$IMAGE_LINK;
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }

                $IMAGE_PATH_NEW = 'C:\xampp\htdocs\FacebookBot\src\resources\original-image.jpg';

                $success = $ImgFetcher->saveImageLocally($true_url, $IMAGE_PATH_NEW);
                if ($success) {
                    // configure with favored image driver (gd by default)
                    Image::configure(array('driver' => 'imagick'));

                    /** @var \Intervention\Image\Image $img */
                    $img = Image::make($IMAGE_PATH_NEW);

                    $ImgTrans = new ImageTransformer();

                    // Transform only once
                    $TRANSFORM_TIMES = 1;
                    $method_params = $ImgTrans->transformRandomly($img, $IMAGE_PATH, $data->getSafety(), $IMAGE_LINK, $TRANSFORM_TIMES);
                } else {
                    $message = 'unable to save image';
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }
            } else {
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__, 1);
            }

            $ImageClassify = new ImageClassifier();

            if (isset($data)) {
                return array(
                    'safety' => $data->getSafety(),
                    'post_title' => $ImageClassify->getPostTitle($method_params, $comment_info, $inform),
                    'post_comment' => $ImageClassify->getPostComment($IMAGE_LINK, $IMAGE_AUTHOR),
                    'comment' => $ImageClassify->getComment($data),
                    'comment_photo' => $ImageClassify->getPhoto($data)
                );
            } else {
                $message =  'no safety: '. $data->getUrl();
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
        } catch (Exception $e) {
            $message =  $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }

        return [];
    }
}

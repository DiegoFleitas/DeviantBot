<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/19/2019
 * Time: 11:47 PM
 */

require_once realpath(__DIR__ . '/../..') . '/vendor/autoload.php';

require_once 'secrets.php';
require_once 'ImageTransformer.php';
require_once 'ImageFetcher.php';
require_once 'CommandInterpreter.php';

use Stringy\Stringy as S;

class FacebookHelper extends DataLogger
{

    /**
     * @param string $_APP_ID
     * @param string $_APP_SECRET
     * @param string $_ACCESS_TOKEN_DEBUG
     * @return \Facebook\Facebook
     */
    function init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG){
        try {
            # v5 with default access token fallback
            $fb = new Facebook\Facebook([
                'app_id' => $_APP_ID,
                'app_secret' => $_APP_SECRET,
                'default_graph_version' => 'v2.10',
            ]);
            $fb->setDefaultAccessToken($_ACCESS_TOKEN_DEBUG);
            return $fb;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }


    /**
     * @param Facebook\Facebook $fb
     * @param string $POST_ID
     * @param bool $PHOTO_COMMENT
     * @param bool $COMMAND_COMMENT
     */
    function getFirstComment($fb, $POST_ID, $PHOTO_COMMENT = false, $COMMAND_COMMENT = false){

        if (!empty($POST_ID)) {
            try {

                // after underscore
                $POST_ID = substr($POST_ID, strpos($POST_ID, "_") + 1);

                $imagequery = '';
                if ($PHOTO_COMMENT) {
                    $imagequery = '?fields=attachment';
                }

                // Returns a `Facebook\FacebookResponse` object
                $response = $fb->get('/' . $POST_ID . '/comments' . $imagequery);

                $graphEdge = $response->getGraphEdge();
                var_dump($graphEdge->asArray());

                // Iterate over all the GraphNode's returned from the edge
                foreach ($graphEdge as $graphNode) {

                    // ignore blacklisted users
                    $from = $graphNode->getField('from');
                    if (isset($from)) {
                        $name = $from->getField('name');
                        if (isset($name)) {
//                                $blacklist = array('DeviantBot 7245', 'ExampleApp');
                            $blacklist = array();
                            if (!in_array($name, $blacklist)) {

                                $message = 'comment made by: ' . $name;
                                $this->logdata($message);

                                // resources
                                if ($PHOTO_COMMENT) {
                                    $attachment = $graphNode->getField('attachment');
                                    if (isset($attachment)) {

                                        // return first photo comment
                                        return $attachment->getField('url');

                                    }
                                } elseif ($COMMAND_COMMENT) {

                                    $text = $graphNode->getField('message');
                                    $comment = S::create($text);
                                    $CI = new CommandInterpreter();
                                    $possiblecommand = $comment->containsAny($CI->getAvailableCommands());
                                    if ($possiblecommand) {
                                        return $text;
                                    }

                                } else {
                                    // return first comment
                                    return $graphNode->getField('message');
                                }

                            } else {
                                $message = 'blacklisted user: ' . $name;
                                $this->logdata($message);
                            }
                        }
                    }


                }

                $message = 'No comments found.';
                $this->logdata('[' . __METHOD__ . ' ERROR] ' . __FILE__ . ':' . __LINE__ . ' ' . $message);

            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                $message = 'Graph returned an error: ' . $e->getMessage();
                $this->logdata('[' . __METHOD__ . ' ERROR] ' . __FILE__ . ':' . __LINE__ . ' ' . $message, 1);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                $message = 'Facebook SDK returned an error: ' . $e->getMessage();
                $this->logdata('[' . __METHOD__ . ' ERROR] ' . __FILE__ . ':' . __LINE__ . ' ' . $message, 1);
            }
        }
    }

    /**
     * @param Facebook\Facebook $fb
     * @param string $ID_REFERENCE
     * @param string $COMMENT
     * @param string $COMMENT_PHOTO
     */
    function postCommentToReference($fb, $ID_REFERENCE, $COMMENT, $COMMENT_PHOTO){

//        $POST_ID = '276699869694101';
//        $COMMENT_ID = '276699869694101_276797539684334';
//
//        $MESSAGE = 'maybe.';

        try {

            $data = array ();

            if(!empty($COMMENT)){
                $data['message'] = $COMMENT;
            }

            if(!empty($COMMENT_PHOTO)){
                $data['source'] = $fb->fileToUpload($COMMENT_PHOTO);
            }

            // $ID_REFERENCE Could either be a post or a comment
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->post($ID_REFERENCE.'/comments', $data);

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }

    }

    /**
     * @param Facebook\Facebook $fb
     * @param string $POST_ID
     * @return \Facebook\GraphNodes\GraphNode
     */
    function getPost($fb, $POST_ID){

        try {

            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/'.$POST_ID);

            return $response->getGraphNode();

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }

    }

    /**
     * @param Facebook\Facebook $fb
     * @param string $IMAGE_PATH
     * @param string $MESSAGE
     * @param string $COMMENT
     * @param string $COMMENT_PHOTO
     */
    function newPost($fb, $IMAGE_PATH, $MESSAGE, $COMMENT, $COMMENT_PHOTO){

        try {

            $fbfile = $fb->fileToUpload($IMAGE_PATH);

            # fileToUpload works with remote and local images
            $data = array(
                'source' => $fbfile,
                'message' => $MESSAGE
            );

            $response = $fb->post('/me/photos', $data);

            // if data has been passed post comment
            if(!empty($COMMENT) || !empty($COMMENT_PHOTO)){

                $graphNode = $response->getGraphNode();
                $post_id = $graphNode->getField('id');
                $this->postCommentToReference($fb, $post_id, $COMMENT, $COMMENT_PHOTO);
            }

            // Close stream so we are able to unlink the image later
            $fbfile->close();

            // Move image to avoid posting it again
            // Formatted this way so files get sorted correctly
            copy($IMAGE_PATH, 'posted/'.date("Y-m-d H_i_s").'.jpg');
            if(unlink($IMAGE_PATH)){
                $this->logdata('the file was copied and deleted.');
            } else {
                $this->logdata('the file couldn\'t deleted.');
            }

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }

    }

    /**
     * @param Facebook\Facebook $fb
     * @return mixed
     */
    function getLastPost($fb){

        try {

            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get(
                '/me/feed'
            );

            $graphEdge = $response->getGraphEdge();
//            var_dump($graphEdge->asArray());

            // Iterate over all the GraphNode's returned from the edge
            foreach ($graphEdge as $graphNode) {
                // avoid polls
                $story = $graphNode->getField('story');
                if(strpos($story, 'poll') === false){
                    $post_id = $graphNode->getField('id');
                    return $post_id;
                }
            }

            $message = 'No valid post found.';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }


    /**
     * @param Facebook\Facebook $fb
     * @return mixed
     */
    function firstCommentFromLastPost($fb){

        $post = $this->getLastPost($fb);

        $raw_comment = $this->getFirstComment($fb, $post);

        return $raw_comment;

    }

    /**
     * @param Facebook\Facebook $fb
     * @return mixed
     */
    function firstCommandFromLastPost($fb){

        $post = $this->getLastPost($fb);

        $raw_comment = $this->getFirstComment($fb, $post, false, true);

        //FILTER_SANITIZE_STRING: Strip tags, optionally strip or encode special characters.
        //FILTER_FLAG_STRIP_LOW: strips bytes in the input that have a numerical value <32, most notably null bytes and other control characters such as the ASCII bell.
        //FILTER_FLAG_STRIP_HIGH: strips bytes in the input that have a numerical value >127. In almost every encoding, those bytes represent non-ASCII characters such as ä, ¿, 堆 etc
        $safe_comment = filter_var($raw_comment, FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

        return $safe_comment;

    }

    /**
     * @param Facebook\Facebook $fb
     * @return mixed
     */
    function firstPhotocommentFromLastPost($fb){

        $post = $this->getLastPost($fb);

        $raw_comment = $this->getFirstComment($fb, $post, true, false);

        return $raw_comment;

    }

}
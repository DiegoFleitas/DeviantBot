<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/19/2019
 * Time: 11:47 PM
 */

require_once realpath(__DIR__ . '../../../..'). '\vendor\autoload.php';
require_once 'resources/secrets.php';
require_once 'Classes/CommandInterpreter.php';

use Stringy\Stringy as S;

class FacebookHelper extends DataLogger
{

    /**
     * @param string $_APP_ID
     * @param string $_APP_SECRET
     * @param string $_ACCESS_TOKEN_DEBUG
     * @return \Facebook\Facebook
     */
    public function init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG)
    {
        try {
            # v5 with default access token fallback
            $fb = new \Facebook\Facebook([
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
        $message = 'something when wrong at initializing Facebook object';
        $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $POST_ID
     * @param bool $PHOTO_COMMENT
     * @param bool $COMMAND_COMMENT
     * @return array
     */
    public function getFirstComment($fb, $POST_ID, $PHOTO_COMMENT = false, $COMMAND_COMMENT = false)
    {
        if (!empty($POST_ID)) {
            try {
                // after underscore
                // TODO: whats the deal with this
                $POST_ID = substr($POST_ID, strpos($POST_ID, "_") + 1);

                $imagequery = '';
                if ($PHOTO_COMMENT) {
                    $imagequery = '?fields=attachment';
                }

                /** @var $response \Facebook\FacebookResponse */
                $response = $fb->get('/' . $POST_ID . '/comments' . $imagequery);

                /** @var $graphEdge \Facebook\GraphNodes\GraphEdge */
                $graphEdge = $response->getGraphEdge();
                var_dump($graphEdge->asArray());

                // Iterate over all the GraphNode's returned from the edge
                /** @var $graphNode \Facebook\GraphNodes\GraphNode */
                foreach ($graphEdge as $graphNode) {
                    // ignore blacklisted users
                    /** @var $from \Facebook\GraphNodes\GraphNode */
                    $from = $graphNode->getField('from');
                    if (isset($from)) {
                        $name = $from->getField('name');
                        if (isset($name)) {
//                            $blacklist = array('DeviantBot 7245', 'ExampleApp');
                            $blacklist = array();
                            if (!in_array($name, $blacklist)) {
                                $text = $graphNode->getField('message');
                                $text = strtolower($text);

                                /** @var Stringy\Stringy $comment */
                                $comment = S::create($text);

                                // resources
                                if ($PHOTO_COMMENT) {
                                    $attachment = $graphNode->getField('attachment');
                                    if (isset($attachment)) {
                                        $message = 'comment made by: ' . $name;
                                        $this->logdata($message);

                                        // return first photo comment
                                        $photo = $attachment->getField('url');
                                        return [
                                            'who'   => $name,
                                            'text'  => '',
                                            'photo' => $photo
                                        ];
                                    }
                                } elseif ($COMMAND_COMMENT) {
                                    $CI = new CommandInterpreter();
                                    $possiblecommand = $comment->startsWithAny($CI->getAvailableCommands());
                                    $length = strlen($comment);
                                    if ($possiblecommand && $length <= $CI->getMaxlength() && $length >= $CI->getMinlength()) {
                                        $message = 'comment made by: ' . $name;
                                        $this->logdata($message);


                                        return [
                                            'who'   => $name,
                                            'text'  => $text,
                                            'photo' => ''
                                        ];
                                    }
                                } else {
                                    $message = 'comment made by: ' . $name;
                                    $this->logdata($message);

                                    // return first comment
                                    return [
                                        'who'   => $name,
                                        'text'  => $text,
                                        'photo' => ''
                                    ];
                                }
                            } else {
                                $logmessage = 'blacklisted user: ' . $name;
                                $this->logdata($logmessage);
                            }
                        }
                    }
                }
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                $logmessage = 'Facebook SDK returned an error: ' . $e->getMessage();
                $this->logdata('[' . __METHOD__ . ' ERROR] ' . __FILE__ . ':' . __LINE__ . ' ' . $logmessage, 1);
            }
        }
        return [];
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $ID_REFERENCE
     * @param string $COMMENT
     * @param string $COMMENT_PHOTO
     */
    public function postCommentToReference($fb, $ID_REFERENCE, $COMMENT, $COMMENT_PHOTO = '')
    {
        try {
            $data = array ();

            if (!empty($COMMENT)) {
                $data['message'] = $COMMENT;
            }

            if (!empty($COMMENT_PHOTO)) {
                $data['source'] = $fb->fileToUpload($COMMENT_PHOTO);
            }

            // $ID_REFERENCE Could either be a post or a comment
            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->post($ID_REFERENCE.'/comments', $data);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $POST_ID
     * @return \Facebook\GraphNodes\GraphNode
     */
    // TODO: use this
    public function getPost($fb, $POST_ID)
    {
        try {
            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->get('/'.$POST_ID);

            return $response->getGraphNode();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        return '';
    }

    /**
     * @param \Facebook\Facebook $fb
     * @param string $IMAGE_PATH
     * @param string $POST_TITLE
     * @param string $POST_COMMENT
     * @param string $SAFETY
     * @param string $COMMENT
     * @param string $COMMENT_PHOTO
     */
    public function newPost($fb, $IMAGE_PATH, $POST_TITLE, $POST_COMMENT, $SAFETY, $COMMENT, $COMMENT_PHOTO)
    {

        try {
            $fbfile = $fb->fileToUpload($IMAGE_PATH);

            # fileToUpload works with remote and local images
            $data = array(
                'source' => $fbfile,
                'message' => $POST_TITLE
            );

            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->post('/me/photos', $data);

            /** @var $graphNode \Facebook\GraphNodes\GraphNode */
            $graphNode = $response->getGraphNode();
            $post_id = $graphNode->getField('id');
            // if safe comment the author and original image
            if ($SAFETY == 'nonadult') {
                $this->postCommentToReference($fb, $post_id, $POST_COMMENT, 'debug/test/original-image.jpg');
            } else {
                // unsafe, no pic
                $POST_COMMENT = '[NSFW] '.$POST_COMMENT;
                $this->postCommentToReference($fb, $post_id, $POST_COMMENT);
            }

            // if data has been passed post comment
            if (!empty($COMMENT) || !empty($COMMENT_PHOTO)) {
                $this->postCommentToReference($fb, $post_id, $COMMENT, $COMMENT_PHOTO);
            }

            // Close stream so we are able to unlink the image later
            $fbfile->close();

            // Move image to avoid posting it again
            // Formatted this way so files get sorted correctly
            copy($IMAGE_PATH, 'debug/posted/'.date("Y-m-d H_i_s").'.jpg');
            if (unlink($IMAGE_PATH)) {
                $this->logdata('the file was copied and deleted.');
            } else {
                $this->logdata('the file couldn\'t deleted.');
            }
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @return string
     */
    public function getLastPost($fb)
    {

        try {

            /** @var $response \Facebook\FacebookResponse */
            $response = $fb->get(
                '/me/feed'
            );

            /** @var $graphEdge \Facebook\GraphNodes\GraphEdge */
            $graphEdge = $response->getGraphEdge();
//            var_dump($graphEdge->asArray());

            /** @var $graphNode \Facebook\GraphNodes\GraphNode */
            foreach ($graphEdge as $graphNode) {
                // avoid polls
                $story = $graphNode->getField('story');
                if (strpos($story, 'poll') === false) {
                    return $graphNode->getField('id');
                }
            }

            $message = 'No valid post found.';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        return '';
    }


    /**
     * @param \Facebook\Facebook $fb
     * @return array
     */
    public function firstCommentFromLastPost($fb)
    {
        $post = $this->getLastPost($fb);

        if (!empty($post)) {
            return $this->getFirstComment($fb, $post);
        }

        return [];
    }

    /**
     * @param \Facebook\Facebook $fb
     * @return array
     */
    public function firstCommandFromLastPost($fb)
    {
        $post = $this->getLastPost($fb);

        $res = $this->getFirstComment($fb, $post, false, true);
        if (!empty($res)) {
            //FILTER_SANITIZE_STRING: Strip tags, optionally strip or encode special characters.
            //FILTER_FLAG_STRIP_LOW: strips bytes in the input that have a numerical value <32, most notably null bytes and other control characters such as the ASCII bell.
            //FILTER_FLAG_STRIP_HIGH: strips bytes in the input that have a numerical value >127. In almost every encoding, those bytes represent non-ASCII characters such as ä, ¿, 堆 etc
            $safe_comment = filter_var($res['text'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

            $res['text'] = strtolower($safe_comment);
            return $res;
        } else {
            return [];
        }
    }

    /**
     * @param \Facebook\Facebook $fb
     * @return array
     */
    // TODO use this
    public function firstPhotocommentFromLastPost($fb)
    {
        $post = $this->getLastPost($fb);

        if (!empty($post)) {
            return $this->getFirstComment($fb, $post, true, false);
        }

        return [];
    }
}

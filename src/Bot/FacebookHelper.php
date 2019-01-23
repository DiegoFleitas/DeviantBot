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

class FacebookHelper extends DataLogger
{

    function getFirstPhotoReply($fb, $POST_ID, $getimages){

//        $POST_ID = '276699869694101';
        if(!empty($POST_ID)){
            try {

                $imagequery = '?fields=attachment';

                // Returns a `Facebook\FacebookResponse` object
                $response = $fb->get('/'.$POST_ID.'/comments'.$imagequery);

                $graphEdge = $response->getGraphEdge();

                // Iterate over all the GraphNode's returned from the edge
                foreach ($graphEdge as $graphNode) {
                    $attachment = $graphNode->getField('attachment');
                    if(isset($attachment)){
                        return $attachment->getField('url');
                    }
                }

            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                $message = 'Graph returned an error: ' . $e->getMessage();
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                $message = 'Facebook SDK returned an error: ' . $e->getMessage();
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
        }
    }

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

    function newPost($fb, $IMAGE_PATH, $IMAGE_LINK, $IMAGE_AUTHOR, $COMMENT, $COMMENT_PHOTO){

        try {

            # fileToUpload works with remote and local images
            $data = array(
                'source' => $fb->fileToUpload($IMAGE_PATH),
                'message' => 'Beep Boop I found this, but I think it got corrupted along the way.
                
                Original image: 
                '.$IMAGE_LINK.'
                author: '.$IMAGE_AUTHOR,
            );

            $response = $fb->post('/me/photos', $data);

            // if data has been passed post comment
            if(!empty($COMMENT) || !empty($COMMENT_PHOTO)){

                $graphNode = $response->getGraphNode();
                $post_id = $graphNode->getField('id');
                $this->postCommentToReference($fb, $post_id, $COMMENT, $COMMENT_PHOTO);
            }

            // Move image to avoid posting it again
            // Formatted this way so files get sorted correctly
            copy($IMAGE_PATH, 'posted/'.date("Y-m-d H_i_s").'.jpg');

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }

    }


}
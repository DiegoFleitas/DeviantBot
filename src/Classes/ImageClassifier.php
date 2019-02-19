<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:46 PM
 */

namespace DeviantBot;

use Intervention\Image\ImageManagerStatic as Image;

class ImageClassifier
{

    /**
     * @param string $tags
     * @return bool
     */
    public function classifyByTags($tags)
    {

        //<editor-fold desc="$meme_material and $terrible Arrays">
//        $meme_material = array(
//            'oc',
//            'fatoc',
//            'webcomicoc',
//            'undertaleoc',
//            'sonicoc',
//            'sonicfancharacter',
//            'fan_character',
//            'ocxcanon',
//            'sansxoc',
//        );

        //FIXME this should be done better
        $terrible = array(

            //UNSAFE
            'boobs',
            'breasts',
            'tits',
            'bigboobs',
            'bigbreasts',
            'hugeboobs',
            'hugebreasts',
            'largeboobs',
            'largebreasts',

            //MLP
            'mlp',
            'mylittlepony',
            'vector',
            'equestria',
            'mlpmylittlepony',
            'mylittleponyfriendshipismagic',
            'equestriagirls',
            'mylittleponyfriendship',
            'forgottenfriendship',
            'mlpunicorn',
            'mlpvector',
            'vectormylittlepony',
            'mlpfimmylittleponyfriendship',
            'mlpsunsetshimmer',
            'sunsetshimmer',
            'equestriagirlsrainbowrocks',
            'equestriagirlsfriendshipgames',
            'equestriagirlsforgottenfriendship',

            //FETISHES
            'tickled',
            'musclegirl',
            'infalted',
            'furry',
            'anthro',
            'ocxcanonshipping',
            'barefeet',
            'feet',
            'foot',
            'longlegs',
            'sexy',
            'cutekawaii',
            'footfetish',
            'tickles',
            'tickling',
            'bbw',
            'belly',
            'cake',
            'chubby',
            'chubbygirl',
            'chubbyobese',
            'curvy',
            'expansion',
            'fat',
            'fatass',
            'fatgirl',
            'fatlegs',
            'feed',
            'feedee',
            'feeder',
            'feeding',
            'forcefeeding',
            'gainer',
            'jiggle',
            'lard',
            'lardass',
            'morbidlyobese',
            'obese',
            'obesity',
            'plump',
            'ssbbw',
            'stuffer',
            'stuffing',
            'thighs',
            'tubby',
            'weightgain',
            'wg',
            'plumpgirl',
            'fatsexy',
            'stuffedbelly',
            'thickthighs',
            'thickwomen',
            'chubbybelly',
            'weightgainfat',
            'fatbellygirl',
            'fatcosplay',
            'thick',
            'fatbelliedwoman',
        );
        //</editor-fold>

        $image_tags = explode(', ', $tags);
        $isTerribleImage = array_intersect($image_tags, $terrible);
//        $isMemeMaterial = array_intersect($image_tags, $meme_material);

        if (!empty($isTerribleImage)) {
            return true;
        }
        return false;
    }

    /**
     * @param DeviantImage $devimg
     * @return string
     */
    public function classify($devimg)
    {
        //FIXME start using description too
//        ex: https://www.deviantart.com/zeronis/art/Black-Heart-Valentine-2B-785978771?fbclid=IwAR3-xBPnfmVeMnOm0CF2wc3mJeTN5LdfniMgahOvw6x7SSzEcgHzwuuulXA
//        "NSFW" and "18+" on it

        if ($devimg->getClassification() !== 'n/a') {
            return $devimg->getClassification();
        } else {
            $devimg->setClassification('ok');

            // unsafe
            if ($devimg->getSafety() !== 'nonadult') {
                $devimg->setClassification('nsfw');
            }

            if ($this->classifyByTags($devimg->getTags())) {
                $devimg->setClassification('bad');
            } else {
                $category = $devimg->getCategory();
                if (strpos($category, 'Anthro') !== false) {
                    $devimg->setClassification('bad');
                }
            }

            return $devimg->getClassification();
        }
    }

    /**
     * @param DeviantImage $devimg
     * @return string
     */
    public function getPhoto($devimg)
    {

        $PATH = '';

        $type = $this->classify($devimg);
        if ($type == 'bad') {
            $PATH = 'C:/xampp/htdocs/FacebookBot/src/resources/reactions/negative/'. mt_rand(1, 47).'.jpg';
        } elseif ($type == 'nsfw') {
            $PATH = 'C:/xampp/htdocs/FacebookBot/src/resources/reactions/tempted/'. mt_rand(1, 27).'.jpg';
        }

        // if its worth to react
        if (!empty($PATH)) {
            // Transform local reaction image
            Image::configure(array('driver' => 'imagick'));

            /** @var \Intervention\Image\Image $img */
            $img = Image::make($PATH);

            $image_path = 'debug/test/botcomment_photo.jpg';
            $ImgTrans = new ImageTransformer();
            $ImgTrans->transformRandomly($img, $image_path, 'nonadult', 'reaction-reroll', 1);

            return $image_path;
        }

        return $PATH;
    }

    /**
     * @param DeviantImage $devimg
     * @return string
     */
    public function getComment($devimg)
    {
        $comment = '';
        $type = $this->classify($devimg);
        if ($type == 'bad') {
            $negative = array(
                'No bot has seen worst',
                'Bot does not like.',
                '[|°-°|]'
            );

            $rnd_index = mt_rand(0, count($negative) - 1);
            $comment = $negative[$rnd_index];
        } elseif ($type == 'nsfw') {
            $tempted = array(
                'Oof',
                'Bot likes.',
                '[|°v°|]'
            );

            $rnd_index = mt_rand(0, count($tempted) - 1);
            $comment = $tempted[$rnd_index];
        }

        return $comment;
    }


    /**
     * @param string $params
     * @param array $comment_info
     * @param string $inform
     * @return string
     */
    public function getPostTitle($params, $comment_info, $inform)
    {
        if (!empty($inform)) {
            return 'Beep Boop I found this, but I think it got corrupted along the way.
                    
                    ~'.$params.'~
                    Command: '.$comment_info['text'].' => '.$inform;
        } elseif (!empty($comment_info['text']) && !empty($comment_info['who'])) {
            return 'Beep Boop I found this, but I think it got corrupted along the way.
                    
                    ~'.$params.'~
                    Command: '.$comment_info['text'].' by '.$comment_info['who'];
        } else {
            return 'Beep Boop I found this, but I think it got corrupted along the way.
                    
                    ~'.$params.'~';
        }
    }

    /**
     * @param string $IMAGE_LINK
     * @param string $IMAGE_AUTHOR
     * @return string
     */
    public function getPostComment($IMAGE_LINK, $IMAGE_AUTHOR)
    {
        return 'Original image: 
                '.$IMAGE_LINK.'
                author: '.$IMAGE_AUTHOR;
    }
}

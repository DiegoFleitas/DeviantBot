<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:46 PM
 */

use Intervention\Image\ImageManagerStatic as Image;

class ImageClassifier
{

    /**
     * @param string $tags
     * @return bool
     */
    function classifyByTags($tags){


        //<editor-fold desc="$meme_material and $terrible Arrays">
        $meme_material = array(
            'oc',
            'fatoc',
            'webcomicoc',
            'undertaleoc',
            'sonicoc',
            'sonicfancharacter',
            'fan_character',
            'ocxcanon',
            'sansxoc',
        );

        $terrible = array(

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
        $isMemeMaterial = array_intersect($image_tags, $meme_material);

        if(!empty($isTerribleImage)){
            return true;
        }
        return false;

    }

    /**
     * @param DeviantImage $devimg
     * @return mixed
     */
    function classify($devimg){

        if(null !== $devimg->getClassification()){
            return $devimg->getClassification();
        } else {

            $devimg->setClassification('ok');

            // unsafe
            if($devimg->getSafety() !== 'nonadult'){
                $devimg->setClassification('nsfw');
            }

            if($this->classifyByTags($devimg->getTags())){
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
    function getPhoto($devimg){

        $PATH = '';

        $type = $this->classify($devimg);
        if($type == 'bad'){
            $PATH = 'reactions/negative/'. mt_rand(1, 47).'.jpg';
        } elseif($type == 'nsfw'){
            $PATH = 'reactions/tempted/'. mt_rand(1, 27).'.jpg';
        }

        // if its worth to react
        if($type !== 'ok'){
            // Transform local reaction image
            Image::configure(array('driver' => 'imagick'));

            $img = Image::make($PATH);

            $image_path = 'test/botcomment_photo.jpg';
            $ImgTrans = new ImageTransformer();
            $ImgTrans->TransformRandomly($img, $image_path, 'nonadult', 'reaction-reroll', 1);

            return $image_path;
        }

        return '';

    }

    /**
     * @param DeviantImage $devimg
     * @return mixed|string
     */
    function getComment($devimg){

        $comment = '';
        $type = $this->classify($devimg);
        if($type == 'bad'){

            $negative = array(
                'No bot has seen worst',
                'Bot does not like.',
                '[|°-°|]'
            );

            $rnd_index = mt_rand(0, count($negative) - 1);
            $comment = $negative[$rnd_index];

        } elseif($type == 'nsfw'){

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
     * @param string $IMAGE_LINK
     * @param string $IMAGE_AUTHOR
     * @param string $params
     * @return string
     */
    function getPostMessage($IMAGE_LINK, $IMAGE_AUTHOR, $params){
        return 'Beep Boop I found this, but I think it got corrupted along the way.
                
                ~'.$params.'~
                Original image: 
                '.$IMAGE_LINK.'
                author: '.$IMAGE_AUTHOR;
    }

}
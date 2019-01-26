<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:30 PM
 */

include_once('ImageFetcher.php');

class DeviantImage
{
    private $author_name;
    private $author_url;
    private $provider_name;
    private $provider_url;
    private $safety;
    private $pubdate;
    private $community;
    private $product;
    private $tags;
    private $copyright;
//    TODO: decide which format / resolution to upload and if i should convert the images
    private $width;
    private $height;
    private $imagetype;
    private $thumbnail_url;
    private $thumbnail_width;
    private $thumbnail_height;
    private $thumbnail_url_150;
    private $thumbnail_url_200h;
    private $thumbnail_width_200h;
    private $thumbnail_height_200h;


    /**
     * DeviantImage constructor.
     * @param string $json
     */
    public function __construct($json)
    {
        $data = json_decode($json);
        $this->author_name = $data->author_name;
        $this->author_url = $data->author_url;
        $this->provider_name = $data->provider_name;
        $this->provider_url = $data->provider_url;
        $this->safety = $data->safety;
        $this->pubdate = $data->pubdate;
        $this->community = $data->community;
        if(isset($data->product)) $this->product = $data->product;
        if(isset($data->tags)) $this->tags = $data->tags;
        if(isset($data->copyright)) $this->copyright = $data->copyright;
        $this->width = $data->width;
        $this->height = $data->height;
        if(isset($data->imagetype)) $this->imagetype = $data->imagetype;
        if(isset($data->thumbnail_url)) $this->thumbnail_url = $data->thumbnail_url;
        if(isset($data->category)) $this->category = $data->category;
        $this->classification = null;
        $this->params = null;
    }

    /**
     * @return null
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param null $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }


    /**
     * @return null
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * @param null $classification
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;
    }


    /**
     * @return string
     */
    public function getAuthorName()
    {
        if(isset($this->author_name)) return $this->author_name;
        return 'Uknown';
    }

    /**
     * @param mixed $author_name
     */
    public function setAuthorName($author_name)
    {
        $this->author_name = $author_name;
    }

    /**
     * @return mixed
     */
    public function getAuthorUrl()
    {
        return $this->author_url;
    }

    /**
     * @param mixed $author_url
     */
    public function setAuthorUrl($author_url)
    {
        $this->author_url = $author_url;
    }

    /**
     * @return mixed
     */
    public function getProviderName()
    {
        return $this->provider_name;
    }

    /**
     * @param mixed $provider_name
     */
    public function setProviderName($provider_name)
    {
        $this->provider_name = $provider_name;
    }

    /**
     * @return mixed
     */
    public function getProviderUrl()
    {
        return $this->provider_url;
    }

    /**
     * @param mixed $provider_url
     */
    public function setProviderUrl($provider_url)
    {
        $this->provider_url = $provider_url;
    }

    /**
     * @return mixed
     */
    public function getSafety()
    {
        return $this->safety;
    }

    /**
     * @param mixed $safety
     */
    public function setSafety($safety)
    {
        $this->safety = $safety;
    }

    /**
     * @return mixed
     */
    public function getPubdate()
    {
        return $this->pubdate;
    }

    /**
     * @param mixed $pubdate
     */
    public function setPubdate($pubdate)
    {
        $this->pubdate = $pubdate;
    }

    /**
     * @return mixed
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @param mixed $community
     */
    public function setCommunity($community)
    {
        $this->community = $community;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param mixed $copyright
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getImagetype()
    {
        return $this->imagetype;
    }

    /**
     * @param mixed $imagetype
     */
    public function setImagetype($imagetype)
    {
        $this->imagetype = $imagetype;
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnail_url;
    }

    /**
     * @param mixed $thumbnail_url
     */
    public function setThumbnailUrl($thumbnail_url)
    {
        $this->thumbnail_url = $thumbnail_url;
    }

    /**
     * @return mixed
     */
    public function getThumbnailWidth()
    {
        return $this->thumbnail_width;
    }

    /**
     * @param mixed $thumbnail_width
     */
    public function setThumbnailWidth($thumbnail_width)
    {
        $this->thumbnail_width = $thumbnail_width;
    }

    /**
     * @return mixed
     */
    public function getThumbnailHeight()
    {
        return $this->thumbnail_height;
    }

    /**
     * @param mixed $thumbnail_height
     */
    public function setThumbnailHeight($thumbnail_height)
    {
        $this->thumbnail_height = $thumbnail_height;
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl150()
    {
        return $this->thumbnail_url_150;
    }

    /**
     * @param mixed $thumbnail_url_150
     */
    public function setThumbnailUrl150($thumbnail_url_150)
    {
        $this->thumbnail_url_150 = $thumbnail_url_150;
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl200h()
    {
        return $this->thumbnail_url_200h;
    }

    /**
     * @param mixed $thumbnail_url_200h
     */
    public function setThumbnailUrl200h($thumbnail_url_200h)
    {
        $this->thumbnail_url_200h = $thumbnail_url_200h;
    }

    /**
     * @return mixed
     */
    public function getThumbnailWidth200h()
    {
        return $this->thumbnail_width_200h;
    }

    /**
     * @param mixed $thumbnail_width_200h
     */
    public function setThumbnailWidth200h($thumbnail_width_200h)
    {
        $this->thumbnail_width_200h = $thumbnail_width_200h;
    }

    /**
     * @return mixed
     */
    public function getThumbnailHeight200h()
    {
        return $this->thumbnail_height_200h;
    }

    /**
     * @param mixed $thumbnail_height_200h
     */
    public function setThumbnailHeight200h($thumbnail_height_200h)
    {
        $this->thumbnail_height_200h = $thumbnail_height_200h;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:30 PM
 */

namespace DeviantBot;

class DeviantImage
{
    private $url = '';
    private $author_name = '';
    private $author_url = '';
    private $provider_name = '';
    private $provider_url = '';
    private $safety = '';
    private $pubdate = '';
    private $community = '';
    private $product = '';
    private $tags = '';
    private $copyright = '';
    private $width = '';
    private $height = '';
    private $imagetype = '';
    private $thumbnail_url = '';
    private $thumbnail_width = '';
    private $thumbnail_height = '';
    private $thumbnail_url_150 = '';
    private $thumbnail_url_200h = '';
    private $thumbnail_width_200h = '';
    private $thumbnail_height_200h = '';
    private $classification = '';
    private $category = '';
    private $params = '';


    /**
     * DeviantImage constructor.
     * @param string $json
     * @param string $url
     */
    public function __construct($json, $url)
    {
        $this->url = $url;
        $data = json_decode($json);
        $this->author_name = $data->author_name;
        $this->author_url = $data->author_url;
        $this->provider_name = $data->provider_name;
        $this->provider_url = $data->provider_url;
        $this->safety = $data->safety;
        $this->pubdate = $data->pubdate;
        $this->community = $data->community;
        if (isset($data->product)) {
            $this->product = $data->product;
        }
        if (isset($data->tags)) {
            $this->tags = $data->tags;
        }
        if (isset($data->copyright)) {
            $this->copyright = $data->copyright;
        }
        $this->width = $data->width;
        $this->height = $data->height;
        if (isset($data->imagetype)) {
            $this->imagetype = $data->imagetype;
        }
        if (isset($data->thumbnail_url)) {
            $this->thumbnail_url = $data->thumbnail_url;
        }
        if (isset($data->category)) {
            $this->category = $data->category;
        }
        $this->classification = 'n/a';
        $this->params = '';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * @param string $classification
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
        if (isset($this->author_name)) {
            return $this->author_name;
        }
        return 'Uknown';
    }

    /**
     * @return string
     */
    public function getAuthorUrl()
    {
        return $this->author_url;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->provider_name;
    }

    /**
     * @return string
     */
    public function getProviderUrl()
    {
        return $this->provider_url;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getPubdate()
    {
        return $this->pubdate;
    }

    /**
     * @return string
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnail_url;
    }

    /**
     * @return string
     */
    public function getThumbnailWidth()
    {
        return $this->thumbnail_width;
    }

    /**
     * @return string
     */
    public function getThumbnailHeight()
    {
        return $this->thumbnail_height;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl150()
    {
        return $this->thumbnail_url_150;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl200h()
    {
        return $this->thumbnail_url_200h;
    }

    /**
     * @return string
     */
    public function getThumbnailWidth200h()
    {
        return $this->thumbnail_width_200h;
    }

    /**
     * @return string
     */
    public function getThumbnailHeight200h()
    {
        return $this->thumbnail_height_200h;
    }
}

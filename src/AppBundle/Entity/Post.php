<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 */
class Post
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetimetz")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="posts")
     * @ORM\JoinColumn(name="theme_id", referencedColumnName="id")
     */
    private $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="intro", type="text")
     */
    private $intro;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="youtube_url", type="string", length=255, nullable=true)
     */
    private $youtubeUrl;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="posts")
     * @ORM\JoinTable(name="posts_tags")
     */
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }



    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Post
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Post
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set theme
     *
     * @param string $theme
     *
     * @return Post
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param $image
     *
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }



    /**
     * Set intro
     *
     * @param string $intro
     *
     * @return Post
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * Get intro
     *
     * @return string
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set youtubeUrl
     *
     * @param string $youtubeUrl
     *
     * @return Post
     */
    public function setYoutubeUrl($youtubeUrl)
    {
        $this->youtubeUrl = $youtubeUrl;

        return $this;
    }

    /**
     * Get youtubeUrl
     *
     * @return string
     */
    public function getYoutubeUrl()
    {
        return $this->youtubeUrl;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }


}


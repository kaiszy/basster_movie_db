<?php

namespace Basster\MovieDbBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Basster\MovieDbBundle\Entity\StorageLocation
 *
 * @ORM\Table(name="storage_location")
 * @ORM\Entity
 */
class StorageLocation
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="Movie", mappedBy="storageLocation", cascade={"persist", "remove"})
     */
    private $movies;
    
    public function __construct() {
        $this->movies = new ArrayCollection();
    }
    
    public function __toString() {
        return $this->title;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * Add movies
     *
     * @param Basster\MovieDbBundle\Entity\Movie $movies
     */
    public function addMovie(\Basster\MovieDbBundle\Entity\Movie $movies)
    {
        $this->movies[] = $movies;
    }

    /**
     * Get movies
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMovies()
    {
        return $this->movies;
    }
}
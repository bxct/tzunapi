<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Carrier
 */
class Carrier extends \BaseEntity
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var integer
     */
    private $active;


    /**
     * Set title
     *
     * @param string $title
     * @return Carrier
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
     * Set created
     *
     * @param \DateTime $created
     * @return Carrier
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Carrier
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    
        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
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
     * Get active
     *
     * @return \DateTime 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set active
     * 
     * @param integer $active
     * @return integer 
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }
}

<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Device
 */
class Device extends \BaseEntity
{
    /**
     * @var integer
     */
    private $userId;

    /**
     * @var string
     */
    private $esn;

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
     * Set userId
     *
     * @param integer $userId
     * @return Device
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set esn
     *
     * @param string $esn
     * @return Device
     */
    public function setEsn($esn)
    {
        $this->esn = $esn;
    
        return $this;
    }

    /**
     * Get esn
     *
     * @return string 
     */
    public function getEsn()
    {
        return $this->esn;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Device
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
     * @return Device
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
}

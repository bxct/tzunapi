<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarriersVendor
 */
class CarriersVendor extends \BaseEntity
{
    /**
     * @var integer
     */
    private $carrierId;

    /**
     * @var integer
     */
    private $vendorId;

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
     * Set carrierId
     *
     * @param integer $carrierId
     * @return CarriersVendor
     */
    public function setCarrierId($carrierId)
    {
        $this->carrierId = $carrierId;
    
        return $this;
    }

    /**
     * Get carrierId
     *
     * @return integer 
     */
    public function getCarrierId()
    {
        return $this->carrierId;
    }

    /**
     * Set vendorId
     *
     * @param integer $vendorId
     * @return CarriersVendor
     */
    public function setVendorId($vendorId)
    {
        $this->vendorId = $vendorId;
    
        return $this;
    }

    /**
     * Get vendorId
     *
     * @return integer 
     */
    public function getVendorId()
    {
        return $this->vendorId;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return CarriersVendor
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
     * @return CarriersVendor
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

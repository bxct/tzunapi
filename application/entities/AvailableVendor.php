<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vendor
 */
class AvailableVendor extends \BaseEntity
{

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var \DateTime
     */
    private $activated;

    /**
     * @var \DateTime
     */
    private $disabled;

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
    private $stackSize;
    
    /**
     * @var integer
     */
    private $completed;
    
    /**
     * @var integer
     */
    private $failed;
    
    /**
     * @var integer
     */
    private $carrierId;
    
    /**
     * @var integer
     */
    private $activeJobs;

    /**
     * Get hostname
     *
     * @return string 
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Get activated
     *
     * @return \DateTime 
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * Get disabled
     *
     * @return \DateTime 
     */
    public function getDisabled()
    {
        return $this->disabled;
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
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Get stack size
     *
     * @return integer 
     */
    public function getStackSize()
    {
        return $this->stackSize;
    }
    
    /**
     * Get completed
     *
     * @return integer 
     */
    public function getCompleted()
    {
        return $this->completed;
    }
    
    /**
     * Get failed number
     *
     * @return integer 
     */
    public function getFailed()
    {
        return $this->failed;
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
     * Get carrier ID
     *
     * @return integer 
     */
    public function getCarrierId()
    {
        return $this->carrierId;
    }
    
    /**
     * Get carrier ID
     *
     * @return integer 
     */
    public function getActiveJobs()
    {
        return $this->activeJobs;
    }
}

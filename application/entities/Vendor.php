<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vendor
 */
class Vendor extends \BaseEntity
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
     * Set hostname
     *
     * @param string $hostname
     * @return Vendor
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    
        return $this;
    }

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
     * Set activated
     *
     * @param \DateTime $activated
     * @return Vendor
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
    
        return $this;
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
     * Set disabled
     *
     * @param \DateTime $disabled
     * @return Vendor
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    
        return $this;
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
     * Set created
     *
     * @param \DateTime $created
     * @return Vendor
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
     * @return Vendor
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
     * Set stack size
     *
     * @param integer $stackSize
     * @return Vendor
     */
    public function setStackSize($stackSize)
    {
        $this->stackSize = $stackSize;
    
        return $this;
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
     * Set completed
     *
     * @param integer $stackSize
     * @return Vendor
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    
        return $this;
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
     * Set failed
     *
     * @param integer $failed
     * @return Vendor
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;
    
        return $this;
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
     * Sets the id property of object
     * 
     * @param integer $id
     * @return \BaseEntity
     */
    public function setId($id) {
       $this->id = $id;
       return $this;
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
     * Detects if this vendor supports specific carrier
     * 
     * @param integer $carrierId
     * @return boolean
     */
    public function supportsCarrier($carrierId) {
        $existingCarrierVendors = \DS::getEntityManager()->getRepository('\Entities\CarriersVendor')->findBy(array('carrierId' => (int)$carrierId, 'vendorId' => $this->getId()));
        return (count($existingCarrierVendors)>0);
    }
    
    /**
     * Assigns previously unsupported carrier to a vendor
     * 
     * @param integer|string $carrier title or ID of carrier
     * @return \Entities\Vendor
     * 
     * @throws \WrongParametersException
     */
    public function attachCarrier($carrier) {
        if(preg_match('/^[0-9]+$/', $carrier)) {
            $carrier = \DS::getEntityManager()->getRepository('\Entities\Carrier')->find($carrier);
        } else {
            $carrier = \DS::getEntityManager()->getRepository('\Entities\Carrier')->findOneByTitle($carrier);
        }
        //If carrier found ... 
        if($carrier) {
            // ... check if Vendor has already this carrier supported ...
            $existingCarrierVendors = \DS::getEntityManager()->getRepository('\Entities\CarriersVendor')->findBy(array('carrierId' => $carrier->getId(), 'vendorId' => $this->getId()));
            if(empty($existingCarrierVendors)) {
                // ... and create new relation if not
                $carrierVendor = new \Entities\CarriersVendor();
                $carrierVendor->setCarrierId($carrier->getId());
                $carrierVendor->setVendorId($this->getId());
                \DS::getEM()->persist($carrierVendor);
            } else {
                throw \ExceptionHandler::wrongParametersException(__('Vendor already supports this carrier.'));
            }
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Could not find appropriate carrier.'));
        }
        
        return $this;
    }
    
    /**
     * Detach supported carrier from a vendor
     * 
     * @param integer|string $carrier title or ID of carrier
     * @return \Entities\Vendor
     * 
     * @throws \WrongParametersException
     */
    public function detachCarrier($carrier) {
        if(isValueNumeric($carrier)) {
            $carrier = \DS::getEntityManager()->getRepository('\Entities\Carrier')->find($carrier);
        } else {
            $carrier = \DS::getEntityManager()->getRepository('\Entities\Carrier')->findOneByTitle($carrier);
        }
        //If carrier found ... 
        if($carrier) {
            // ... check if Vendor has already this carrier supported ...
            $existingCarrierVendors = \DS::getEntityManager()->getRepository('\Entities\CarriersVendor')->findBy(array('carrierId' => $carrier->getId(), 'vendorId' => $this->getId()));
            if(empty($existingCarrierVendors)) {
                throw \ExceptionHandler::wrongParametersException(__('Vendor does not support this carrier.'));
            } else {
                foreach($existingCarrierVendors as &$carrierVendor) {
                    \DS::getEntityManager()->remove($carrierVendor);
                }
            }
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Could not find appropriate carrier.'));
        }
        
        return $this;
    }
}

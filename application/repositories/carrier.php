<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * VendorRepository
 */
class Carrier extends EntityRepository {
    
    /**
     * Retrieves a list of currently active carriers
     * 
     * @param string $hostname
     * @return \Entities\Vendor
     */
    public function findActive() {
        $returnCarriers = array();
        if($carriers = parent::findBy(array('active' => 1))) {
            foreach($carriers as $carrier) {
                $returnCarriers[$carrier->getId()] = $carrier;
            }
        }
        return $returnCarriers;
    }
    
}
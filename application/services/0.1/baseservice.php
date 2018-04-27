<?php
/**
 * This file holds parent service class.
 * Provides authorisation and few utility functions
 * 
 * @author Anton Matiyenko <amatiyenko@gmail.com>
 * 
 * @ignore
 */

namespace Services_0_1;

/**
 * Class which provides properties and methods common for all the services API supplies.
 * Has to be inherited by every service class.
 * 
 * @ignore
 */
class BaseService implements \Interfaces\ApiService {

    /**
     * Holds the instance of User entity detected by his public key
     * 
     * @var \Entities\User
     */
    protected $authorizedUser = null;
    
    /**
     * Determines if the request was protected with appropriate signature
     * 
     * @var boolean
     */
    protected $sealed = false;

    /**
     * Standard constructor - common for all services
     * 
     * @param \Entities\User $authData
     * @param boolean $sealed
     */
    public function __construct($authData = null, $sealed = false) {
        $this->authorizedUser = $authData;
        $this->sealed = $sealed;
    }
    
    /**
     * Informs if a user with specified associated public key is identified
     * 
     * @return boolean
     */
    protected function userDetected() {
        return !empty($this->authorizedUser);
    }
    
    /**
     * Detects whether or not request was protected with proper signature
     * 
     * @return boolean
     */
    protected function sealed() {
        return $this->sealed;
    }
    
    /**
     * Detects if user is not a guest (some User entry is found)
     * 
     * @return boolean
     */
    protected function authorized() {
        return ($this->userDetected() && $this->sealed());
    }
    
    /**
     * Checks if ESN has valid format and which type of ESN it has
     * 
     * @param string $esn
     * 
     * @return boolean|string
     */
    public static function checkEsn($esn) {
        /**
         * @todo this is a dummy replace with more details and guess on which carrier ESN may rely to
         */
        return true;
    }
}

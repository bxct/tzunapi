<?php

/**
 * @ignore
 */

namespace Services_0_1;

/**
 * Default (dummy) service
 * 
 * @ignore
 */
class Hello extends \Services_0_1\BaseService {

    /**
     * Default method
     * 
     * @return string
     */
    public function index() {
        return __('Welcome to Tsunami v.0.1');
    }
    
    /**
     * First of all, a test method to check remote client capabilities
     * 
     * @param type $sealed
     * @param type $user_id
     * @return \stdClass
     */
    public function private_index($sealed, $user_id) {
        $response = new \stdClass();
        $response->sealed = $sealed;
        if($sealed) {
            $response->username = \DS::getEM()->getRepository('\Entities\User')->find($user_id)->getUsername();
        }
        return $response;
    }

}

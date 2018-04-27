<?php

/**
 * @ignore
 */

namespace Services_0_1;

/**
 * @ignore
 */
class Vendors extends \Services_0_1\BaseService {
    
    /**
     * Creates new Vendor instance
     * 
     * @param string $request_method
     * 
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     */
    public static function add($request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Create database entry and deploy a new pre-adjusted AWS box right inside (or provision Vagrant box or whatever!)
        if($newVendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->create()) {
            $response = new \stdClass();
            $response->newVendor = $newVendor;
            $response->message = __('New vendor instance was created');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor creation failed.'));
        }
        
        return $response;
    }
    
    /**
     * Enables specified Vendor entry in database, so it may again receive new jobs
     * 
     * @param integer|string $vendor ID or hostname of Vendor to Enable
     * @param string $request_method
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public static function enable($vendor=false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Enable (activated=YES disabled=NO) Vendor if host is accessible
        if($vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->activate($vendor)) {
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('Vendor is now active.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }
    
    /**
     * Disables specified Vendor entry in database, so it does not receive any new jobs
     * 
     * @param integer|string $vendor ID or hostname of Vendor to Enable
     * @param string $request_method
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public static function disable($vendor=false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Enable (activated=YES disabled=NO) Vendor if host is accessible
        if($vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->disable($vendor)) {
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('Vendor is now disabled.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }
    
    public static function resume($vendor=false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Enable (activated=YES disabled=NO) Vendor if host is accessible
        if($vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->resume($vendor)) {
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('Vendor is now active.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }
    
    /**
     * Destroys the vendor instance and removes the vendor
     * 
     * @param integer|string $vendor ID or hostname of Vendor to destroy
     * @param string $request_method
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public static function destroy($vendor=false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Destroy the vendor instance and remove the database entry
        if($vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->destroy($vendor)) {
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('Vendor was destroyed.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }
    
    /**
     * Retrieves the status of vendor AWS instance
     * 
     * @param integer|string $vendor ID or hostname of Vendor to check status of
     * @param string $request_method
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public static function status($vendor=false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Retrieve the status of instance
        if($vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->status($vendor)) {
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('Vendor was destroyed.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }

    /**
     * Attaches new Carrier support to a Vendor
     * 
     * @param string|integer $vendor hostname or ID of vendor
     * @param string|integer $carrier title or ID of carrier
     * @param string $request_method
     * 
     * @return \stdClass
     * 
     * @throws \NoAccessException
     */
    public static function attach_carrier($vendor = false, $carrier = false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        if(isValueNumeric($vendor)) {
            $vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->find($vendor);
        } else {
            $vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->findOneByHostname($vendor);
        }
        
        //Create associations between vendor (if such vendor exists) and provided carriers (if they exist as well)
        if($vendor) {
            $vendor->attachCarrier($carrier);
            \DS::getEM()->flush();
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('New carrier was assigned.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }
    
    /**
     * Detaches Carrier support from a Vendor
     * 
     * @param string|integer $vendor hostname or ID of vendor
     * @param string|integer $carrier title or ID of carrier
     * @param string $request_method
     * 
     * @return \stdClass
     * 
     * @throws \NoAccessException
     */
    public static function detach_carrier($vendor = false, $carrier = false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }

        if(isValueNumeric($vendor)) {
            $vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->find($vendor);
        } else {
            $vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->findOneByHostname($vendor);
        }
        
        if($vendor) {
            $vendor->detachCarrier($carrier);
            \DS::getEM()->flush();
            $response = new \stdClass();
            $response->vendor = $vendor;
            $response->message = __('Carrier support was removed.');
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Vendor not found'));
        }
        
        return $response;
    }

}

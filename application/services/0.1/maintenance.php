<?php

/**
 * @ignore
 */

namespace Services_0_1;

/**
 * @ignore
 */
class Maintenance extends \Services_0_1\BaseService {

    /**
     * Default operation: gives the list of existing operations for this service in output
     * 
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return null
     * 
     * @throws \NoAccessException
     */
    public function index($request_method) {
        
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        $serviceContent = file_get_contents(__FILE__);
        $availableOperations = array();
        preg_match_all('/[(public\s)]*function\s([a-zA-Z0-9\_]*)[\(]/', $serviceContent, $fileFunctions);
        if(array_key_exists(1, $fileFunctions)) {
            foreach($fileFunctions[1] as $functionName){
                if($functionName == 'index') {
                    continue;
                }
                preg_match('/' . $functionName . '\((.*)\)/', $serviceContent, $argumentMatches);
                $arguments = array();
                if(array_key_exists(1, $argumentMatches)) {
                    $arguments = array_map(function($elt){return trim($elt);}, explode(',', $argumentMatches[1]));
                    if(!empty($arguments)) {
                        foreach($arguments as $k => $arg) {
                            if(strpos($arg, '=')) {
                                $argParts = array_map(function($elt){return trim($elt);}, explode('=', $arg));
                                if(in_array($argParts[0], array('$request_method', '$user_id', '$sealed')))  {
                                    unset($arguments[$k]);
                                    continue;
                                }
                                $arguments[str_replace('$', '', $argParts[0])] = $argParts[1];
                            } else {
                                $arguments[str_replace('$', '', $arg)] = false;
                            }
                            unset($arguments[$k]);
                        }
                    }
                }
                $availableOperations[$functionName] = $arguments;
            }
        }
        
        $response = array();
        $response[] = "\r\n";
        $response[] = __('Welcome to Tsunami v.0.1 Maintenance console');
        $response[] = "\r\n";
        $response[] = __('Available operations are:');
        $response[] = "\r\n";
        
        foreach($availableOperations as $operationName => $parameters) {
            $functionBlock = array();
            $functionBlock[] = __('Accepts parameters:');
            foreach($parameters as $parameterName => $default){
                $functionBlock[] = "\t - " . $parameterName;
            }
            $response[] = implode("\r\n", $functionBlock);
        }
        $response[] = "\r\n";
        
        echo print_r(implode("\r\n", $response), true);
        
        return;
    }
    
    /**
     * Retrieves status of last query with given ESN
     * 
     * @param string $esn the ESN number of the device
     * @param integer $user_id the ID of authorized use (always tsunamicli in this case)
     * @param boolean $sealed (system-overridden) the request must be properly sealed to accomplish => 
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass the complete overall and per-carrier status details of ESN query
     * <pre>
     * class stdClass#80 (4) {
     *     public $status => string "in_progress"
     *     public $status_details => NULL
     *     public $carriers =>
     *     array(4) {
     *         'Verizon' =>
     *             array(3) {
     *               'status' => string(11) "in_progress"
     *               'status_details' => NULL
     *               'carrier_title' => string(7) "Verizon"
     *             }
     *         'AT&T' =>
     *             array(3) {
     *               'status' => string(11) "in_progress"
     *               'status_details' => NULL
     *               'carrier_title' => string(4) "AT&T"
     *             }
     *         'Sprint' =>
     *             array(3) {
     *               'status' => string(11) "in_progress"
     *               'status_details' => NULL
     *               'carrier_title' => string(6) "Sprint"
     *             }
     *         'T-Mobile' =>
     *             array(3) {
     *               'status' => string(11) "in_progress"
     *               'status_details' => NULL
     *               'carrier_title' => string(8) "T-Mobile"
     *             }
     *       }
     *     public $device_status =>
     *     string(5) "clean"
     * }
     * </pre>
     * 
     * @throws \NoAccessException
     */
    public function query_status($esn = false, $user_id = false, $sealed = false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        $response = \Services_0_1\Query::poll($esn, $user_id, $sealed);
        return $response;
    }
    
    /**
     * Creates new Carrier entry
     * 
     * @param string $title title of brand of the carrier to be added
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdObject
     * <pre>
     * class stdClass#57 (2) {
     *     public $message => string(57) "New carrier "Blue Wireless" has been created. The ID is:5"
     *     public $new_carrier_id => int(5)
     * }
     * </pre>
     * 
     * @throws \NoAccessException
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     */
    public function add_carrier($title = false, $request_method = false) {
        $response = \Services_0_1\Carriers::add($title, $request_method);
        return $response;
    }
    
    /**
     * Enables existing carrier
     * 
     * @param string|integer $carrier title or ID of carrier to be found on the database
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass
     * <pre>
     * class stdClass#58 (1) {
     *     public $message => string(43) "Carrier "Blue Wireless" has been activated."
     * }
     * </pre>
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     * @throws \MissingParameterException
     */
    public function enable_carrier($carrier = false, $request_method = false) {
        $response = \Services_0_1\Carriers::enable($carrier, $request_method);
        return $response;
    }
    
    /**
     * Disables existing carrier
     * 
     * @param string|integer $carrier title or ID of carrier
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass
     * <pre>
     * class stdClass#60 (1) {
     *     public $message => string(42) "Carrier "Blue Wireless" has been disabled."
     * }
     * </pre>
     * 
     * @throws \NoAccessException
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     */
    public function disable_carrier($carrier = false, $request_method = false) {
        $response = \Services_0_1\Carriers::disable($carrier, $request_method);
        return $response;
    }
    
    /**
     * Creates new Vendor instance
     * 
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     */
    public function add_vendor($request_method = false) {
        return \Services_0_1\Vendors::add($request_method);
    }
    
    /**
     * Enables specified Vendor entry in database, so it may again receive new jobs
     * 
     * @param integer|string $vendor ID or hostname of Vendor to Enable
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public function enable_vendor($vendor=false, $request_method = false) {
        return \Services_0_1\Vendors::enable($vendor, $request_method);
    }
    
    /**
     * Disables specified Vendor entry in database, so it does not receive any new jobs
     * 
     * @param integer|string $vendor ID or hostname of Vendor to Enable
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * @return \stdClass
     * 
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public function disable_vendor($vendor=false, $request_method = false) {
        return \Services_0_1\Vendors::disable($vendor, $request_method);
    }
    
    /**
     * Restarts the previously halted vendor
     * 
     * @param string|integer $vendor hostname or ID of vendor
     * @param type $request_method
     * @return string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     */
    public function resume_vendor($vendor=false, $request_method = false) {
        return \Services_0_1\Vendors::resume($vendor, $request_method);
    }
    
    /**
     * Attaches new Carrier support to a Vendor
     * 
     * @param string|integer $vendor hostname or ID of vendor
     * @param string|integer $carrier title or ID of carrier
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass
     * 
     * @throws \NoAccessException
     */
    public function add_vendor_carrier($vendor = false, $carrier = false, $request_method = false) {
        return \Services_0_1\Vendors::attach_carrier($vendor, $carrier, $request_method);
    }
    
    /**
     * Detaches Carrier support from a Vendor
     * 
     * @param string|integer $vendor hostname or ID of vendor
     * @param string|integer $carrier title or ID of carrier
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass
     * 
     * @throws \NoAccessException
     */
    public function detach_vendor_carrier($vendor = false, $carrier = false, $request_method = false) {
        return \Services_0_1\Vendors::detach_carrier($vendor, $carrier, $request_method);
    }
    
    /**
     * Cancels the active query
     * 
     * @param string $esn the ESN number of the device you want to cancel query for
     * @param integer $user_id the ID of authorized use (always tsunamicli in this case)
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass standard response
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public function cancel_query($esn = false, $user_id = false, $sealed = false, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        return \Services_0_1\Query::cancel($esn, $user_id, $sealed);
    }
    
    /**
     * Creates a new user entry. Generates key pair and password if necessary.
     * 
     * @param string $username username of future user
     * @param string $user_public_key public key to assign to user
     * @param string $user_private_key private key to assign to user
     * @param string $password password for the account being created
     * @param boolean $generate_password flag, telling the script if password should be generated automatically in case the it was not specified explicitly
     * @param string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return \stdClass
     * <pre>
     * class stdClass#47 (5) {
     *     public $message => string "[MESSAGE]"
     *     public $username => string "[SPECIFIED USERNAME]"
     *     public $password => string "[SPECIFIED OR GENERATED PASSWORD]"
     *     public $publicKey => string "[GENERATED PUBLIC KEY]"
     *     public $privateKey => string "[GENERATED PRIVATE KEY]"
     * }
     * </pre>
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public function add_user($username = false, $user_public_key = false, $user_private_key = false, $password = false, $generate_password = true, $request_method = false) {
        
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        return \Services_0_1\User::add($username, $user_public_key, $user_private_key, $password, $generate_password, $request_method);
    }
    
    /**
     * Verifies username and password pair
     * 
     * @param string $username username of user to look up in the database
     * @param string $password corresponding password
     * @param string string $request_method (system-overridden) must be 'CLI' => operation is called from the CLI exclusively. Is not accessible via 'POST', 'GET' etc.
     * 
     * @return boolean
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public function check_user_authorization($username = false, $password = false, $request_method = false) {
        
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        return \Services_0_1\User::auth($username, $password);
    }

}

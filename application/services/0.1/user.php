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
class User extends \Services_0_1\BaseService {
    
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
    public static function add($username = false, $user_public_key = false, $user_private_key = false, $password = false, $generate_password = true, $request_method = false) {
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        if(!$username) {
            throw \ExceptionHandler::missingParameterException(__('You must specify username'));
        }
        if(!$password && !$generate_password) {  
            throw \ExceptionHandler::missingParameterException(__('You must specify password'));
        }
        //Check if username already exists
        if($user = \DS::getEM()->getRepository('\Entities\User')->findByUsername($username)) {
            throw \ExceptionHandler::wrongParametersException(__('User with this username already exists'));
        }
        if(empty($user_public_key)) {
            $user_public_key = \PwGen::generateKey();
        }
        if(empty($user_private_key)) {
            $user_private_key = \PwGen::generateKey(29);
        }
        if(!$password) {
            $password = \PwGen::generatePassword();
        }
        //Check if public key is already occupied
        if($user = \DS::getEM()->getRepository('\Entities\User')->findByPublicKey($user_public_key)) {
            throw \ExceptionHandler::wrongParametersException(__('User with this public key already exists'));
        }
        
        $user = new \Entities\User();
        
        $user->setUsername($username);
        $user->setPassword(\Signet::hmac_sha1(\Config::read('password_salt'), $password));
        $user->setPublicKey($user_public_key);
        $user->setPrivateKey($user_private_key);
        
        \DS::getEntityManager()->persist($user);
        \DS::getEntityManager()->flush();
        
        $response = new \stdClass();
        $response->message = __('New user has been created');
        
        $response->username = $username;
        $response->password = $password;
        $response->publicKey = $user_public_key;
        $response->privateKey = $user_private_key;
        
        return $response;
    }

    /**
     * Verifies username and password pair
     * 
     * @param string $username
     * @param string $password
     * 
     * @return boolean
     * 
     * @throws type
     */
    public static function auth($username = false, $password = false, $loginToken = false, $request_method = false) {
        
        /**
         * @todo add password check token support to avoid bruteforcing of the usersname/password and move away from CLI-only approach
         */
        
        //Block maintenance from access as regular API service
        if($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        if(!$username) {
            throw \ExceptionHandler::missingParameterException(__('You must specify username'));
        }
        if(!$password) {
            throw \ExceptionHandler::missingParameterException(__('You must specify password'));
        }
        
        if($user = \DS::getEM()->getRepository('\Entities\User')->findBy(array('username' => $username, 'password' => \Signet::hmac_sha1(\Config::read('password_salt'), $password)))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @todo Generate token associated with specified client. Limit number of valid tokens per on request
     */
    public static function get_login_token() {
        
    }
}

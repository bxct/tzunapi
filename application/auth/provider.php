<?php

namespace Auth;

class Provider {

    /**
     * Retrieves data on \User who attempts to do API call
     * 
     * @param string $publicKey
     * @return \Entities\User|false
     */
    public static function getUser($publicKey) {
        $user =  false;
        if($publicKey) {
            $user = \DS::getEntityManager()->getRepository('Entities\User')->findBy(array('publicKey' => $publicKey));
            if(is_array($user) && array_key_exists(0, $user)) {
                $user = $user[0];
            }
        }
        return $user;
    }

}

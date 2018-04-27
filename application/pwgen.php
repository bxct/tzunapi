<?php

/**
 * Random password generator
 */
class PwGen {

    /**
     * Set of symbols collections
     * To increase/descrease complication of the password, it is possible to mix in variations of the symbols into each collection,
     * so e.g. numbers can have more letters to make final password contain less numbers and more letters etc.
     * @var array
     */
    static $symbols = array(
        //Numbers
        '5432167890',
        //Symbols
        '~!@#$%^&*()*-qwertysdfghjcvbnm12345678', //Alphanumeric symbols added primarily to decrease complication of the final string
        //Upper-case symbols
        'QWERTYUIOPLKJHGFDSAZXCVMB',
        //Lower-case
        'qwertyupoiasdfghjklmnbvcxz'
    );

    /**
     * Generates complex password phrase relying on pseudo-random picking of collections and symbols from collections
     * 
     * @param integer $length
     * @return string
     */
    public static function generatePassword($length = 12) {
        $password = '';
        for ($i = 0; $i < intval($length); $i++) {
            $collection = self::$symbols[rand(0, count(self::$symbols)-1)];
            $password .= $collection[rand(0, (strlen($collection) - 1))];
        }
        return $password;
    }
    
    /**
     * Randomly private/public key
     * 
     * @param integer $length
     * @return string
     */
    public static function generateKey($length = 24) {
        return str_replace(array('&', '@', '(', ')', '^', '$', '#'), '*', self::generatePassword($length));
    }

}

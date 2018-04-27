<?php

/**
 * Singleton class to provide read/write access to all properties of initial configuration
 */
class Config {

    /**
     * Includes the list of properties
     * 
     * @var array
     */
    protected static $_config = array();

    /**
     * Writes a property into the properties list
     * 
     * @param string $configKey
     * @param mixed $value
     */
    public static function write($configKey, $value) {
        self::$_config[$configKey] = $value;
    }

    /**
     * Reads a property out of existing list
     * 
     * @param string $configKey
     * @param mixed $default
     * @return mixed
     */
    public static function read($configKey, $default = false) {
        if (array_key_exists($configKey, self::$_config)) {
            return self::$_config[$configKey];
        }
        return $default;
    }

}

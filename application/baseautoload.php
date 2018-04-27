<?php

/**
 * Performs lookup throughout available libs according to namespace and class name
 */
class BaseAutoload {
    
    /**
     * Prepares and includes the file path for a Entity class
     * 
     * @param array $parts
     * @param boolean $require
     */
    public static function entity($parts, $require) {
        $fileName = array_pop($parts) . '.php';
        $path = FS_ROOT . FS_DS . 'application' . FS_DS . strtolower(implode(FS_DS, $parts)) . FS_DS . $fileName;
        self::includeFile($path, $require);
        return $path;
    }

    /**
     * Prepares and includes the file path for a service class
     * 
     * @param array $parts
     * @param boolean $require
     */
    public static function service($parts, $require) {
        //Handle the version of the Service specified in the classname paths
        $namespaceDetails = explode('_', array_shift($parts));
        $fileName = array_pop($parts);
        $version = array_shift($namespaceDetails);
        array_unshift($parts, implode('.', $namespaceDetails));
        array_unshift($parts, $version);
        $path = FS_ROOT . FS_DS . 'application' . FS_DS . self::preparePath(array_merge($parts, array($fileName)));
        self::includeFile($path, $require);
        return $path;
    }

    /**
     * Provides system-wide autoloading alorithm
     * 
     * @param string $className
     * @param boolean $require
     */
    public static function prepare($className, $require = false) {
        if($parts = explode('\\', $className)) {
            if(empty($parts[0])) {
                array_shift($parts);
            }
        }
        switch (true) {
            case strpos($parts[0], 'Services') === 0:
                return self::service($parts, $require);
                break;
            case strpos($parts[0], 'Entities') === 0:
                return self::entity($parts, $require);
                break;
            default:
                $path = FS_ROOT . FS_DS . 'application' . FS_DS . self::preparePath($parts);
                self::includeFile($path, $require);
                return $path;
                break;
        }
        return false;
    }

    /**
     * Organizes the default way to compose a file path to be included in script
     * 
     * @param array $parts
     * @return string
     */
    public static function preparePath($parts) {
        return strtolower(implode(FS_DS, $parts)) . '.php';
    }

    /**
     * Includes (or requires, depending on settings) a PHP file
     * 
     * @param string $path Absolute path of a PHP file
     * @param boolean $require Determines whether or not the file must be *required* in the script
     * @return boolean
     */
    public static function includeFile($path, $require = false) {
        if (is_readable($path) && is_file($path)) {
            if ($require) {
                require_once $path;
            } else {
                include_once $path;
            }
            return true;
        }
        return false;
    }

}

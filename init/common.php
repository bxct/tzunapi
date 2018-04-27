<?php

//Filesystem constants
if (!defined('FS_DS')) {
    define('FS_DS', DIRECTORY_SEPARATOR);
}

if (!defined('FS_ROOT')) {
    define('FS_ROOT', dirname(__DIR__));
}

if (!defined('FS_WEBROOT')) {
    define('FS_WEBROOT', FS_ROOT . FS_DS . 'webroot');
}

if (!defined('FS_DOCTRINE')) {
    define('FS_DOCTRINE', FS_ROOT . FS_DS . 'vendor' . FS_DS . 'doctrine');
}

//Basic autoload requirements
require_once FS_ROOT . FS_DS . 'application' . FS_DS . 'baseautoload.php';
require_once FS_ROOT . FS_DS . 'vendor' . FS_DS . 'autoload.php';

//Default autoloader for the system besides what Composer wants - goes first
spl_autoload_register(array('\BaseAutoload', 'prepare'));

//Enable Doctrine autoloader function - supposedly second
Doctrine\ORM\Tools\Setup::registerAutoloadDirectory(FS_DOCTRINE . FS_DS . 'common' . FS_DS . 'lib');

//Configure application
require_once FS_ROOT . FS_DS . 'init' . FS_DS . 'config.php';

if(is_readable(FS_ROOT . FS_DS . 'init' . FS_DS . 'client.config.php')) {
    require_once FS_ROOT . FS_DS . 'init' . FS_DS . 'client.config.php';
}

set_error_handler(array('\ExceptionHandler', 'factory'));
register_shutdown_function(array('\ExceptionHandler', 'factory'));

//Debugging
call_user_func(
    /**
     * Callable closure => no useless vars in global scope!!!
     */
    function($debug) {
        $displayErrors = 'off';
        $errorReporting = false;
        if ($debug) {
            if (is_string($debug)) {
                /**
                 * @todo Handle logging only and other usecases of debugging
                 */
                ;
            } else {
                $displayErrors = 'on';
                $errorReporting = E_ALL;
            }
        }
        if (function_exists('ini_set')) {
            ini_set('display_errors', $displayErrors);
        }
        error_reporting($errorReporting);
    }, Config::read('debug')
);

/**
 * Operates with i18n parameters of the application
 * 
 * @param string $string
 * @return string A string translated according to the language settings
 */
function __($string) {
    /**
     * @todo fill in required i18n capabilities here if necessary
     */
    return utf8_encode($string);
}

//Default timezone for the application
date_default_timezone_set(\Config::read('default_timezone'));

/**
 * Checks if a value contains only numbers
 * 
 * @param mixed $value
 * @param array $matches
 * @return boolean
 */
function isValueNumeric($value, &$matches = array()){
    return preg_match('/^[0-9]+$/', (string)$value, $matches);
}
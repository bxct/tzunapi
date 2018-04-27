<?php

class TsunamiException extends \Exception {
    
    protected $err_msg = "";
    protected $err_code = "";
    protected $err_severity = "";
    protected $err_file = "";
    protected $err_line = "";
    protected $err_context = array();
    
    public function __construct ($err_msg = false, $err_code = false, $err_severity = false, $err_file = false, $err_line = false, $err_context = array()) {
        foreach (get_defined_vars() as $vName => $vValue) {
            if($vValue) {
                $this->$vName = $vValue;
            }
        }
        static::log(get_class($this), $err_msg, $err_code, $err_severity, $err_file, $err_line);
        parent::__construct($err_msg, $err_code);
    }
    
    /**
     * General function to log exception contents
     * 
     * @param string $class
     * @param string $err_msg
     * @param integer $err_code
     * @param string $err_severity
     * @param string $err_file
     * @param integer $err_line
     * @return boolean
     */
    public static function log($class, $err_msg, $err_code, $err_severity, $err_file, $err_line) {
        return \Logger::error(self::getFormattedMessage($class, $err_msg, $err_code, $err_severity, $err_file, $err_line));
    }
    
    /**
     * Composes log/output message out of available error information details
     * 
     * @param string $class
     * @param string $err_msg
     * @param integer $err_code
     * @param integer $err_severity
     * @param string $err_file
     * @param integer $err_line
     * @param array $err_context
     * 
     * @return string
     */
    public static function getFormattedMessage($class = false, $err_msg = false, $err_code = false, $err_severity = false, $err_file = false, $err_line = false, $err_context = array()) {
        return '<b>' . $class . '</b>' . ': (code:' . $err_code . ') ' . '(severity:' . $err_severity . '):' . "\r\n           " . $err_msg . "\r\n           " . 'in <b><i>' . $err_file . '</b></i> on line ' . $err_line . "\r\n";
    }
    
    /**
     * Returns message formatted for client output
     * 
     * @param type $err_msg
     * @param string $err_msg
     * @param integer $err_code
     * @param integer $err_severity
     * @param string $err_file
     * @param integer $err_line
     * @param array $err_context
     * 
     * @return string
     */
    public function getOutputMessage($err_msg = false, $err_code = false, $err_severity = false, $err_file = false, $err_line = false, $err_context = array()) {
        foreach (get_defined_vars() as $vName => $vValue) {
            if(!$vValue) {
                if($this->$vName) {
                    $$vName = $this->$vName;
                }
            }
        }
        return nl2br($this->getFormattedMessage(get_class($this), $err_msg, $err_code, $err_severity, $err_file, $err_line));
    }
}


class TsunamiRequestException extends \TsunamiException {
    /**
     * Overrides parent log function in order to write into request log instead of error
     * 
     * @param string $class
     * @param string $err_msg
     * @param integer $err_code
     * @param string $err_severity
     * @param string $err_file
     * @param integer $err_line
     * @return boolean
     */
    public static function log($class, $err_msg, $err_code, $err_severity, $err_file, $err_line) {
        return \Logger::request(self::getFormattedMessage($class, $err_msg, $err_code, $err_severity, $err_file, $err_line));
    }
}

/**
 * System-specific Exception classes
 */
class TsunamiFatalError extends \TsunamiException {}

class NoAccessException extends \TsunamiRequestException {}

class ValidationException extends \TsunamiRequestException {}

class MissingParameterException extends \TsunamiRequestException {}

class WrongParametersException extends \TsunamiRequestException {}

class SystemFailureException extends \TsunamiException {}

class RequestFailed extends \TsunamiRequestException {}

/**
 * Classes for generic PHP error reporting
 */
class TsunamiErrorException extends \TsunamiException {}

class TsunamiWarningException extends \TsunamiException {}

class TsunamiParseException extends \TsunamiException {}

class TsunamiNoticeException extends \TsunamiException {}

class TsunamiCoreErrorException extends \TsunamiException {}

class TsunamiCoreWarningException extends \TsunamiException {}

class TsunamiCompileErrorException extends \TsunamiException {}

class TsunamiUserErrorException extends \TsunamiException {}

class TsunamiUserWarningException extends \TsunamiException {}

class TsunamiUserNoticeException extends \TsunamiException {}

class TsunamiStrictException extends \TsunamiException {}

class TsunamiRecoverableErrorException extends \TsunamiException {}

class TsunamiDeprecatedException extends \TsunamiException {}

class TsunamiUserDeprecatedException extends \TsunamiException {}

/**
 * Class which includes logic to handle all kinds of errors appearing in the system
 */
class ExceptionHandler extends \Exception {

    public function __construct($message, $code, $previous) {
        ;
    }

    public static function fatal($message) {
        return self::exception($message);
    }

    public static function exception($message) {
        return new \Exception($message);
    }

    /**
     * Stands as general PHP error reporting. Overrides any other error handler on the system
     * 
     * @param int $err_severity
     * @param string $err_msg
     * @param string $err_file
     * @param int $err_line
     * @param array $err_context
     * @return boolean
     * @throws boolean
     */
    public static function factory($err_severity = false, $err_msg = false, $err_file = false, $err_line = false, array $err_context = array()) {
        
        if(!$err_severity && !$err_msg && !$err_file && !$err_line) {
            if($error = error_get_last()) {
                list($err_severity, $err_msg, $err_file, $err_line) =  array_values($error);
            }
        }

        $exception = false;
        
        switch($err_severity) {
            case E_ERROR: 
                $exception = new \TsunamiErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_WARNING: 
                $exception = new \TsunamiWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_PARSE: 
                $exception = new \TsunamiParseException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_NOTICE: 
                $exception = new \TsunamiNoticeException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_CORE_ERROR: 
                $exception = new \TsunamiCoreErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_CORE_WARNING: 
                $exception = new \TsunamiCoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_COMPILE_ERROR: 
                $exception = new \TsunamiCompileErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_COMPILE_WARNING: 
                $exception = new \TsunamiCoreWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_USER_ERROR: 
                $exception = new \TsunamiUserErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_USER_WARNING: 
                $exception = new \TsunamiUserWarningException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_USER_NOTICE: 
                $exception = new \TsunamiUserNoticeException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_STRICT: 
                $exception = new \TsunamiStrictException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_RECOVERABLE_ERROR: 
                $exception = new \TsunamiRecoverableErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_DEPRECATED: 
                $exception = new \TsunamiDeprecatedException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
            case E_USER_DEPRECATED: 
                $exception = new \TsunamiUserDeprecatedException($err_msg, 0, $err_severity, $err_file, $err_line);
                break;
        }
         
        if(\Config::read('debug') && $exception) {
            if(\Config::read('strict_reporting')) {
                throw $exception;
            } else {
                echo $exception->getOutputMessage();
            }
        }
        
        return true;
    }

    public static function noAccessException($message) {
        return new \NoAccessException($message);
    }
    
    public static function validationException($message) {
        return new \ValidationException($message);
    }
    
    public static function missingParameterException($message){
        return new \MissingParameterException($message);
    }
    
    public static function wrongParametersException($message){
        return new \WrongParametersException($message);
    }
    
    public static function systemFailureException($message) {
        return new \SystemFailureException($message);
    }
    
    public static function requestFailed($message) {
        return new \RequestFailed($message);
    }

}

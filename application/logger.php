<?php

/**
 * Class which performs all types of logging
 */
class Logger {

    /**
     * The list of supported types of logs
     * 
     * @todo What f*cking for?
     * 
     * @var array
     */
    private static $supportedTypes = array(
        'error',
        'debug',
        'custom',
        'request',
        'response',
        'dispatcher'
    );

    /**
     * Writes the data into a log of supplied type. Acceps only supported type of logs
     * 
     * @param string $message
     * @param string $type
     * @return boolean
     */
    public static function write($message, $type = 'debug', $customTypeName = '') {
        if (in_array($type, self::$supportedTypes)) {
            
            //Pretty anything
            if($type == 'custom' && !empty($customTypeName)) {
                $type = $customTypeName;
            }
            
            $folderPath = FS_ROOT . FS_DS . 'logs';
            if(!file_exists($folderPath)) {
                if(!is_writable($folderPath) || !mkdir($folderPath)) {
                    return false;
                }
            }
            
            $today = date('Y-m-d');
            if(\Config::use_type_folder_for_logging()) {
                $folderPath = FS_ROOT . FS_DS . 'logs' . FS_DS . $type;
                if(!file_exists(FS_ROOT . FS_DS . 'logs' . FS_DS . $type)) {
                    if(!is_writable($folderPath) || !mkdir($folderPath)) {
                        return false;
                    }
                }
                $filePath = $folderPath . FS_DS . $today . '.log';
            } else {
                $filePath = $folderPath . FS_DS . $type . '_' . $today . '.log';
            }
            
            if(file_exists($filePath) ) {
                if(!is_writable($filePath)) {
                    return false;
                }
                if(!$logFile = fopen($filePath, 'a')) {
                    return false;
                }
                fwrite($logFile, date('H:i:s - ') . print_r($message, true) . "\r\n");
                fclose($logFile);
            } else {
                if(!is_writable($folderPath)) {
                    return false;
                }
                if(!$logFile = fopen($filePath, 'a')) {
                    return false;
                }
                fwrite($logFile, date('H:i:s - ') . print_r($message, true) . "\r\n");
                fclose($logFile);
                @chmod($filePath, 0777);
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * Writes debug log
     * 
     * @param string $message
     * @return boolean
     */
    public static function debug($message) {
        return self::write($message, 'debug');
    }
    
    /**
     * Writes error log
     * 
     * @param string $message
     * @return boolean
     */
    public static function error($message) {
        return self::write($message, 'error');
    }
    
    /**
     * Writes request log
     * 
     * @param mixed $data
     * @return boolean
     */
    public static function request($data) {
        $message = print_r($data, true);
        return self::write($message, 'request');
    }
    
    /**
     * Writes response log
     * 
     * @param mixed $data
     * @return boolean
     */
    public static function response($data) {
        $message = print_r($data, true);
        return self::write($message, 'response');
    }

}

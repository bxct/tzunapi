<?php

namespace OutputProcessors;

/**
 * Class containing logic to pack output data as JSON string
 */
class Html implements \Interfaces\OutputProcessor {
    
    private $service = '';
    
    private $operation = '';
    
    public function setExtras($service, $operation) {
        $this->service = $service;
        $this->operation = $operation;
    }
    
    /**
     * Generates JSON output in a standard API response form
     * 
     * @param string $status overall processing status
     * @param mixed $output any output to be encoded
     * @return string
     * @throws Exception
     */
    public function generate($status, $output) {
        if(function_exists('json_encode')) {
            $response = new\stdClass();
            $response->status = $status;
            $response->body = $output;
            return json_encode($response);
        } else {
            throw \ExceptionHandler::exception(__('JSON extension is now available for PHP'));
        }
    }
}

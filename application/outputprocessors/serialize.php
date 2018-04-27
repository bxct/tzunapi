<?php

namespace OutputProcessors;

/**
 * Class which generates serialized string from verious response formats for output
 */
class Serialize implements \Interfaces\OutputProcessor, \Serializable {
    
    public $status = false;
    public $body = false;
    
    /**
     * Serializes output as a string in a standard API response form
     * 
     * @param string $status overall processing status
     * @param mixed $output any output to be encoded
     * @return string
     * @throws Exception
     */
    public function generate($status, $output) {
        $this->status = $status;
        $this->body = $output;
        return $this->serialize();
    }
    
    /**
     * Implements standard method for data serialization
     * 
     * @return string
     */
    public function serialize() {
        if (is_object($this->body) && method_exists($this->body, 'dump')) {
            $this->body = get_object_vars($this->body->dump());
        }
        return serialize(get_object_vars($this));
    }

    /**
     * Implements standard interface method. In fact is a dummy, as response does not have to be unserialized back
     * 
     * @param string $data
     * @return boolean
     */
    public function unserialize($data) {
        return false;
    }

}

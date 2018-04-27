<?php

require_once('../../init/common.php');

class TestHereAndNow implements \JsonSerializable {

    private $something = 'Query has been canceled';

    public function jsonSerialize() {
        $obj = new \stdClass();
        $obj->x = json_decode($this->something);
        return $obj;
    }

}

class JsonTest extends \PHPUnit_Framework_TestCase {
    
    protected $outputProcessor = null;
    
    public function __construct() {
        $this->outputProcessor = new \OutputProcessors\Json();
    }
    
    public function test_encode() {
        $query = new \Entities\Query();
        $query->setCanceled(new \DateTime());
        $query->setStatus('canceled');
        $query->setStatusDetails('Canceled by client');
        $queries = array(
            $query
        );
        $response = new \stdClass();
        $response->message = __('Query has been canceled');
        $response->query = $queries;
        $jsonResponse = json_encode($response);
        if ($this->assertNotEquals(false, $jsonResponse, 'JSON error: ' . json_last_error_msg()));
    }
    
    public function test_dummy(){
        $response = new \stdClass();
        $response->message = __('Query has been canceled');
        $response->query = json_decode('Query has been canceled');
        
        $jsonResponse = json_encode($response);

        if ($this->assertNotEquals(false, $jsonResponse, 'JSON error: ' . json_last_error_msg()));
    }
    
    public function test_dummy2(){
        
        $jsonResponse = json_encode(json_decode('Query has been canceled'));

        if ($this->assertNotEquals(false, $jsonResponse, 'JSON error: ' . json_last_error_msg()));
    }
    
    public function test_dummy3() {
        
        $test = new \TestHereAndNow();
        $jsonResponse = json_encode($test);

        if ($this->assertEquals(false, $jsonResponse, 'JSON error: ' . json_last_error_msg()));
    }
    
    public function test_encode_with_obj() {
        $query = new \Entities\Query();
        $query->setCanceled(new \DateTime());
        $query->setStatus('canceled');
        
        $statusObj = new \stdClass();
        $statusObj->msg = 'Query has been canceled';
        $query->setStatusDetails(json_encode($statusObj));
        
        $queries = array(
            $query
        );
        
        $response = new \stdClass();
        $response->message = __('Query has been canceled');
        $response->query = $queries;
        
        $jsonResponse = json_encode($response);
        
        if ($this->assertNotEquals(false, $jsonResponse, 'JSON error: ' . json_last_error_msg()));
    }
    
     public function test_encode_with_array() {
        $query = new \Entities\Query();
        $query->setCanceled(new \DateTime());
        $query->setStatus('canceled');
        
        $statusObj = array(
            'msg' => 'Query has been canceled'
        );
        $query->setStatusDetails(json_encode($statusObj));
        
        $queries = array(
            $query
        );
        
        $response = new \stdClass();
        $response->message = __('Query has been canceled');
        $response->query = $queries;
        
        $jsonResponse = json_encode($response);
        
        if ($this->assertNotEquals(false, $jsonResponse, 'JSON error: ' . json_last_error_msg()));
    }
    
}
<?php

require_once('../../init/common.php');

class DispatcherTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException \WrongParametersException
     */
    public function test_run_wrongParametersException1() {
        $data = array(
            'service' => 'Dummy_0_1',
            'operation' => 'get_something'
        );
        
        $dispatcher = new \Dispatcher('POST', $data);
    }
    
    /**
     * @expectedException \WrongParametersException
     */
    public function test_run_wrongParametersException2() {
        $data = array(
            'service' => '_0_1',
            'operation' => 'get_something'
        );
        
        $dispatcher = new \Dispatcher('POST', $data);
    }
    
    
    public function test_run_hello() {
        $data = array(
            'service' => 'Hello_0_1',
            'operation' => 'index'
        );
        
        $dispatcher = new \Dispatcher('POST', $data);
        $response = $dispatcher->run()->response();
        $this->assertEquals('Welcome to Tsunami v.0.1', json_decode($response)->body);
    }
}

?>
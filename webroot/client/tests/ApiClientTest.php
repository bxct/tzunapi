<?php

require_once('../../../application/sdk/common.php');

class ApiClientTest extends \PHPUnit_Framework_TestCase {
    
    private static $d = array (
            'service' => 'bundle_apps_0_1',
            'operation' => 'read',
            'version' => '0.1',
            'bundle_id' => '123',
            'app_id' => '456',
            'id' => '70',
            'submit' => 'Submit',
            'action' => 'bundle_apps_0_1/read/79',
            'test_signature' => '3231312313',
            'something_else' => '33',
            'format' => 'xml',
            'filxxes' =>
                array (
                    'fccile' =>
                        array (
                            'name' => '',
                            'type' => '',
                            'tmp_name' => '',
                            'error' => 4,
                            'size' => 0,
                            'dsd' => array(
                                'random_array' => array(
                                    'ddd' => array(
                                        'a', 
                                        'b', 
                                        'c' => array(
                                            'd', 
                                            'e')
                                    ),
                                ),
                            )
                    ),
                ),
        );
    
    public function test_simple_get() {
        $data = self::$d;
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), 'http://api.tzunapi.dev');
        $response = $apiClient->send($data);
        $this->assertNotEmpty($response, 'Response is empty');
    }

    public function test_simple_post() {
        $data = self::$d;
        $data['operation'] = 'create';
        unset($data['action']);
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), 'http://api.tzunapi.dev');
        $response = $apiClient->send($data, 'post');
        $this->assertNotEmpty($response, 'API call failed');
        $this->assertTrue(is_numeric($response), 'API operation failed');
    }
    
    public function test_simple_post_operation() {
        $data = self::$d;
        $data['operation'] = 'create';
        unset($data['action']);
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), 'http://api.tzunapi.dev');
        $response = $apiClient->send($data, 'post');
        $this->assertTrue(is_numeric($response), 'API operation failed');
    }
    
    public function test_action_guessing() {
        $data = array (
            'format' => 'json',
            'action' => 'bundleapps_0_1/92',
            'service' => 'bundleapps_0_1',
//            'operation' => 'create',
            'id' => '',
            'bundle_id' => '890',
            'app_id' => '123',
            'timestamp' => '1428458105',
            'method' => 'post',
            'version' => '0.1',
        );
        
        $apiClient = new \ApiClient('99999', '88888', 'http://api.tzunapi.dev');
        $response = json_decode($apiClient->send($data, 'post'));
        $this->assertTrue($response->status === 'success' and $response->body, 'API operation with action guessing failed');
        
    }

}

?>
<?php

require_once('../../init/common.php');

class CrapClientTest extends \PHPUnit_Framework_TestCase {

    function test_this() {
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . \Config::read('api_endpoint'));
        $data = array(
            'service' => 'query',
            'operation' => 'status_callback',
            'version' => '0.1',
            'sub_query_id' => 1,
            'status' => 'clean',
            'status_details' => 'weird',
            'format' => 'json'
            );
        $vendorResponse = json_decode($apiClient->send($data, 'POST'));
        var_dump($vendorResponse);
        $this->assertTrue(false);
    }

}

?>
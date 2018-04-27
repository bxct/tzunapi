<?php

require_once('../../init/test.common.php');

class PublishTest extends \PHPUnit_Framework_TestCase {
    
    protected $esn = array(
        'A0000008ACEA1B',
        'A0000002C7D7BB',
        '013331004558909',
        '013439005665737',
        '354439054250982',
        '013334008033845',
    );
    
//    public function test_Amqp() {
////        $this->truncateEverything();
////        $vendors = \DS::getEM()->getRepository('\Entities\Vendor')->findAll();
//        $carriers = \DS::getEM()->getRepository('\Entities\Carrier')->findAll();
//        foreach($this->esn as $esn) {
//            $vendorId = rand(990,1200);
//            $carrierId = $carriers[array_rand($carriers)]->getId();
//            $subQueryId = rand(1,100);
//            $this->assertTrue(\JobPublishers\Amqp::publishJob($esn, $vendorId, $carrierId, $subQueryId));
//        }
//    }
//    
//    public function test_Api() {
//        $this->truncateEverything();
//        $vendors = \DS::getEM()->getRepository('\Entities\Vendor')->findAll();
//        $carriers = \DS::getEM()->getRepository('\Entities\Carrier')->findAll();
//        foreach($this->esn as $esn) {
//            $vendorId = $vendors[array_rand($vendors)]->getId();
//            $carrierId = $carriers[array_rand($carriers)]->getId();
//            $subQueryId = rand(1,100);
//            $this->assertTrue(\JobPublishers\Api::publishJob($esn, $vendorId, $carrierId, $subQueryId));
//        }
//    }
//    
    public function truncateEverything() {
        $queries = \DS::getEM()->getRepository('\Entities\Query')->findAll();
        foreach($queries as $q) {
            \DS::getEM()->remove($q);
        }
        $subQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findAll();
        foreach($subQueries as $q) {
            \DS::getEM()->remove($q);
        }
        $jobs = \DS::getEM()->getRepository('\Entities\Job')->findAll();
        foreach($jobs as $q) {
            \DS::getEM()->remove($q);
        }
        \DS::getEM()->flush();
    }
//    
    public function test_QueryService_Amqp() {
        $this->truncateEverything();
        \Config::write('job_publisher', 'Amqp');
        foreach($this->esn as $esn) {
            $response = \Services_0_1\Query::add($esn, false, false, 6, 1, 1);
            $this->assertEquals('Query has been added.', $response->message);
        }
        $publisherClass = '\JobPublishers\\' . \Config::read('job_publisher');

//        $publisher = new $publisherClass();
//        $jobs = $publisher->fetchJobs(9999);
//        $this->assertEquals(24, count($jobs));
    }
//    
    public function test_QueryService_Api() {
        $this->truncateEverything();
        \Config::write('job_publisher', 'Api');
        foreach($this->esn as $esn) {
            $response = \Services_0_1\Query::add($esn, false, false, 6, 1, 1);
            $this->assertEquals('Query has been added.', $response->message);
        }
        $publisherClass = '\JobPublishers\\' . \Config::read('job_publisher');

//        $publisher = new $publisherClass();
//        $jobs = $publisher->fetchJobs(9999);
//        $this->assertEquals(24, count($jobs));
    }
//    
    public function test_Performance() {
        //AMQP
        $this->truncateEverything();
        $tStart = microtime(true);
        for($i=0;$i<=5;$i++) {
            $this->test_QueryService_Amqp();
        }
        $tDiff1= microtime(true) - $tStart;
        echo "AMQP publisher: ", $tDiff1, "\r\n";
        
        //API
        $this->truncateEverything();
        $tStart = microtime(true);
        for($i=0;$i<=5;$i++) {
            $this->test_QueryService_Api();
        }
        $tDiff2= microtime(true) - $tStart;
        echo "API publisher: ", $tDiff2, "\r\n";
        
        $this->assertLessThan($tDiff2, $tDiff1);
    }
    
//    public function test_process_api() {
//        \Config::write('job_publisher', 'Api');
//        $publisher = new \JobPublishers\Api();
//        $jobs = $publisher->fetchJobs(9999);
//        \Services_0_1\Jobs::process(1, 'CLI');
//        $this->assertEquals((count($jobs)-\Config::read('jobs_limit_per_process_iteration', 3)), count($publisher->fetchJobs(9999)));
//    }
    
//    public function test_process_amqp() {
//        \Config::write('job_publisher', 'Amqp');
//        $publisher = new \JobPublishers\Amqp();
////        $jobs = $publisher->fetchJobs(9999);
//        \Services_0_1\Jobs::process(1, 'CLI');
////        $this->assertEquals((count($jobs)-\Config::read('jobs_limit_per_process_iteration', 3)), count($publisher->fetchJobs(9999)));
//    }

}
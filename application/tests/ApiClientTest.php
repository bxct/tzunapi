<?php

require_once('../../init/common.php');

class ApiClientTest extends \PHPUnit_Framework_TestCase {
    
    var $client;
    
//    public function __construct() {
//        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->find(1);
//        $this->client = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . $vendor->getHostname());
//    }
//    
//    public function test_callback_function (){
//        $qb = \DS::getEM()->createQueryBuilder();
//        $qb
//            ->select(array('v', '(v.stackSize-v.completed-v.failed) AS active_jobs'))
//            ->from('Entities\Vendor', 'v')
//            ->leftJoin(
//                'Entities\CarriersVendor',
//                'cv',
//                \Doctrine\ORM\Query\Expr\Join::WITH,
//                'v.id = cv.vendorId'
//            )
//            ->where('cv.carrierId = :carrier_id')
//            ->setParameter('carrier_id', 2)
//            ->addOrderBy('active_jobs', 'ASC')
//            ->addOrderBy('v.failed', 'ASC')
//            ->setMaxResults(1);
//        if($subQueries = $qb->getQuery()->getResult()) {
//            var_dump($subQueries); exit;
//        }
//        $qb
//            ->select(array('sq AS sub_query', 'c.title AS carrier_title'))
//            ->from('Entities\SubQuery', 'sq')
//            ->innerJoin(
//                'Entities\Carrier',
//                'c',
//                \Doctrine\ORM\Query\Expr\Join::WITH,
//                'sq.carrierId = c.id'
//            )
//            ->where('sq.queryId = :query_id')
//            ->setParameter('query_id', $this->getId())
//            ->orderBy('c.id', 'ASC');
//    }
    
    public function test_connection() {
        //Call frontend back with status updates
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . \Config::read('api_endpoint'));
        $data = array(
            'service' => 'hello',
            'operation' => 'index',
            'version' => '0.1',
            'format' => 'json'
        );
        $vendorResponse = json_decode($apiClient->send($data, 'POST'));
//        print_r($vendorResponse);
        $this->assertEquals('Welcome to Tsunami v.0.1', $vendorResponse->body);
    }
    
    public function test_private_connection() {
        //Call frontend back with status updates
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . \Config::read('api_endpoint'));
        $data = array(
            'service' => 'hello',
            'operation' => 'private_index',
            'version' => '0.1',
            'format' => 'json'
        );
        $vendorResponse = json_decode($apiClient->send($data, 'POST'));
//        var_dump($vendorResponse);
        $this->assertTrue($vendorResponse->body->sealed);
    }

}

?>  `
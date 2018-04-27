<?php

require_once('../../init/common.php');

class VendorRepositoryTest extends \PHPUnit_Framework_TestCase {
//   function timeout_resume_test() {
//       $vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->resume($vendor);
//       $this->assert
//   }
    
    function test_getVendor() {
        
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->findAll()[0];
        
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->getVendor($vendor);
        $this->assertTrue($vendor instanceof \Entities\Vendor);
        
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->getVendor($vendor->getId());
        $this->assertTrue($vendor instanceof \Entities\Vendor);
        
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->getVendor($vendor->getHostname());
        $this->assertTrue($vendor instanceof \Entities\Vendor);
        
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->getVendor('unknownhost.com');
        $this->assertNull($vendor);
    }
    
    function test_status() {
        $actual = \DS::getEM()->getRepository('\Entities\Vendor')->status(8);
        $this->assertEquals('running', $actual);
        
        $actual = \DS::getEM()->getRepository('\Entities\Vendor')->status(7);
        $this->assertEquals('stopped', $actual);
        
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->find(7);
        
        $vendor->setId(5);
        
        $actual = \DS::getEM()->getRepository('\Entities\Vendor')->status($vendor);
        $this->assertEquals('not_created', $actual);
        
//        $actual = \DS::getEM()->getRepository('\Entities\Vendor')->getVendor(12);
//        $this->assertEquals('undefined', $actual);
        
        $actual = \DS::getEM()->getRepository('\Entities\Vendor')->status(1);
        $this->assertEquals(false, $actual);
    }
}

<?php

require_once('../../init/common.php');

class VendorTest extends \PHPUnit_Framework_TestCase {
    
    public function test_supportsCarrier() {
        $vendor = \DS::getEntityManager()->getRepository('\Entities\Vendor')->find(18);
        $this->assertEquals(true, $vendor->supportsCarrier(1), 'Incorrect');
        $this->assertEquals(false, $vendor->supportsCarrier(10), 'Incorrect');
    }
}
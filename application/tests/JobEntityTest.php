<?php

require_once('../../init/common.php');

class JobEntityTest extends \PHPUnit_Framework_TestCase {
    
    public function test_status() {
        $j = new \Entities\Job();
        
        $j->setStatusDetails('This is a simple string');
        $this->assertEquals('This is a simple string', $j->getStatusDetails());
        
        $j->setStatusDetails(array('f1' => 'This is an array element', 'f2' => 'This is something else'));
        $this->assertEquals('This is an array element', $j->getStatusDetails()['f1']);
        
        $j->setStatusDetails(array('regular_status_details' => array('f1' => 'This is an array element', 'f2' => 'This is something else')));
        $this->assertEquals('This is an array element', $j->getStatusDetails()['f1']);
        $this->assertFalse($j->getMoreStatusDetails());
        
        $j->setStatusDetails(array('regular_status_details' => array('f1' => 'This is an array element', 'f2' => 'This is something else'), 'more_status_details' => array('e1' => 'Additional detail')));
        $this->assertEquals('This is an array element', $j->getStatusDetails()['f1']);
        $this->assertEquals('Additional detail', $j->getMoreStatusDetails()['e1']);
    }    
    
}
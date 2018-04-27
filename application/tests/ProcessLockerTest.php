<?php

require_once('../../init/test.common.php');

class ProcessLockerTest extends \PHPUnit_Framework_TestCase {
    
    public function test_lock() {
        $segmentId = ftok(__FILE__, 'x')+1;
        
        $segm = shm_attach($segmentId);
        shm_remove($segm);
        shm_detach($segm);
        
        $this->assertEquals(true, \ProcessLocker::lock($segmentId), "\r\nCould not lock the process\r\n");
        
        $segm = shm_attach($segmentId);
        shm_remove($segm);
        shm_detach($segm);
    }
    
    public function test_unlock() {
        $segmentId = ftok(__FILE__, 'x')+2;
        
        $segm = shm_attach($segmentId);
        shm_remove($segm);
        shm_detach($segm);
        
        $this->assertEquals(true, \ProcessLocker::lock($segmentId), "\r\nCould not lock the process\r\n");
        $this->assertEquals(true, \ProcessLocker::unlock(), "\r\nCould not UNlock the process\r\n");
        $this->assertEquals(true, \ProcessLocker::lock($segmentId), "\r\nCould not lock the process\r\n");
        
        $segm = shm_attach($segmentId);
        shm_remove($segm);
        shm_detach($segm);
    }
    
    public function test_can_not_lock() {
        $segmentId = ftok(__FILE__, 'x')+3;
        
//        $segm = shm_attach($segmentId);
//        shm_remove($segm);
//        shm_detach($segm);
        
        $this->assertEquals(true, \ProcessLocker::lock($segmentId), "\r\nCould not lock the process\r\n");
        $this->assertEquals(false, \ProcessLocker::lock($segmentId), "\r\nCould lock the process\r\n");
        
        sleep(10);
        
        $this->assertEquals(true, \ProcessLocker::unlock(), "\r\nCould not UNlock the process\r\n");
        
//        $segm = shm_attach($segmentId);
//        shm_remove($segm);
//        shm_detach($segm);
    }
    
    public function test_wait_and_lock() {
//        $segmentId = ftok(__FILE__, 'x')+4;
//        
////        $segm = shm_attach($segmentId);
////        shm_remove($segm);
////        shm_detach($segm);
//        
//        $this->assertEquals(true, \ProcessLocker::lockAndWaitIfNecessary($segmentId), "\r\nCould not lock the process\r\n");
//        $this->assertEquals(true, \ProcessLocker::unlock(), "\r\nCould not UNlock the process\r\n");
//        $this->assertEquals(true, \ProcessLocker::lock($segmentId), "\r\nCould not lock the process\r\n");
//        
//        $segm = shm_attach($segmentId);
//        shm_remove($segm);
//        shm_detach($segm);
    }

}
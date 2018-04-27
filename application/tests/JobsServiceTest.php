<?php

require_once('../../init/test.common.php');

class JobsServiceTest extends \PHPUnit_Framework_TestCase {
    
    var $ESNSubstr = '546738934564738245673852364782345672852736456784237645647283746';
    var $carriers = array(
        1 => 'Sit',
        2 => 'Lorem',
        3 => 'Ipsum',
        4 => 'Dolor',
        5 => 'Amet',
        6 => 'Adipiscing',
        7 => 'Consectetur',
        8 => 'Elit',
    );
    
    public function testProcessImprovementWithoutRabbit() {
        
        $esn = substr($this->ESNSubstr, rand(0, strlen($this->ESNSubstr)-9), 10);        
        
        //////// COUNT
        
        $qb = \DS::getEM()->createQueryBuilder();
        $qb->select('count(job.id)');
        $qb->from('Entities\Job', 'job');

        $countInitial = $qb->getQuery()->getSingleScalarResult();
        
        ////////
        
        $jobsStackSize = 100;
        for($i=0;$i<$jobsStackSize;$i++) {
            \Services_0_2rc1\Jobs::add($esn, rand(2,50), array_rand($this->carriers), 123, true);
        }
        
        ////////////////////////////////////////
        ////////////////////////////////////////
        
//        $tStart = microtime(true);  
//        for($i=0;$i<ceil($jobsStackSize/(2*\Config::read('jobs_limit_per_process_iteration', 5)));$i++) {
//            \Services_0_2rc1\Jobs::process(true, 'CLI');
//        }
//        $tDiff1_2 = microtime(true) - $tStart;
        
        ////////////////////////////////////////
        ////////////////////////////////////////
        
        $tStart = microtime(true);
        \Services_0_1\Jobs::process(true, 'CLI');
        $tDiff1_1 = microtime(true) - $tStart;
        
        echo "\r\nOld: " . $tDiff1_1 . "\r\n";
//        echo "\r\nNew: " . $tDiff1_2 . "\r\n";
        
        $this->assertTrue($tDiff1_2<1.1);
        
        ////////////////////////////////////////
        ////////////////////////////////////////
        
        //////// COUNT
        
        $qb = \DS::getEM()->createQueryBuilder();
        $qb->select('count(job.id)');
        $qb->from('Entities\Job', 'job');

        $count = $qb->getQuery()->getSingleScalarResult();
        
        ////////
        
        $this->assertEquals($count-$countInitial, 100);
        
    }
    
    public function testProcessImrpovementWithRabbit() {
        
    }
}

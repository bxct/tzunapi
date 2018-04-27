<?php

require_once('../../init/common.php');

class Services01QueryTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException \MissingParameterException
     */
    public function test_add_MissingParameterException() {
        \Services_0_1\Query::add(false);
    }
    
    /**
     * @expectedException \NoAccessException
     */
    public function test_add_NoAccessException1() {
        \Services_0_1\Query::add('something');
    }
    
    /**
     * @expectedException \NoAccessException
     */
    public function test_add_NoAccessException2() {
        //$esn = false, $carrier = false, $carrier_id = false, $user_id = false, $force = false, $sealed = false
        \Services_0_1\Query::add('something',false,false,1,false);
    }
    
    public function test_add() {

        //AT&T text
        $response = \Services_0_1\Query::add('123123123123', 'AT&T', false, 1, 1, 1);
        
        $this->assertInstanceOf('\Entities\Query', $response->query);
        
        $this->assertNotEmpty($sq = \DS::getEm()->getRepository('\Entities\SubQuery')->findByQueryId($response->query->getId()));
        
        $this->assertEquals(1, count($sq));
        
        //AT&T integer
        $response = \Services_0_1\Query::add('333331313333333', 2, false, 1, 1, 1);
        
        $this->assertInstanceOf('\Entities\Query', $response->query);
        
        $this->assertNotEmpty($sq = \DS::getEm()->getRepository('\Entities\SubQuery')->findByQueryId($response->query->getId()));
        
        $this->assertEquals(1, count($sq));
        
        //All carriers
        $response = \Services_0_1\Query::add('1231215553123123', false, false, 1, 1, 1);
        
        $this->assertInstanceOf('\Entities\Query', $response->query);
        
        $this->assertNotEmpty($sq = \DS::getEm()->getRepository('\Entities\SubQuery')->findByQueryId($response->query->getId()));
        
        $this->assertEquals(4, count($sq));
        
    }

}

?>
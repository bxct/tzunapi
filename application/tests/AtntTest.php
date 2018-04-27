<?php

require_once('../../init/common.php');

class AtntTest extends \PHPUnit_Framework_TestCase {
    
    private $scraper = null;
    
    public function __construct() {
        $this->scraper = new \Scrapers\Atnt(new GuzzleHttp\Client());
    }
    
    public function test_process() {
        
        $imei = 'A0000008ACEA1B';               
        $this->assertEquals('clean', $response = $this->scraper->process($imei)['status'], 'Unexpected status');
        
        $imei = 'A0000002C7D7BB';               
        $this->assertEquals('blacklisted', $response = $this->scraper->process($imei)['status'], 'Unexpected status');
        
        $imei = '013331004558909';//good        
        $this->assertEquals('clean', $response = $this->scraper->process($imei)['status'], 'Unexpected status');
                 
        $imei = '013439005665737';//good
        $this->assertEquals('clean', $response = $this->scraper->process($imei)['status'], 'Unexpected status');
        
        $imei = '354439054250982';//bad, lost, stolen        
        $this->assertEquals('blacklisted', $response = $this->scraper->process($imei)['status'], 'Unexpected status');

        $imei = '013334008033845';//bad, lost, stolen
        $this->assertEquals('blacklisted', $response = $this->scraper->process($imei)['status'], 'Unexpected status');       
    }    
    
}
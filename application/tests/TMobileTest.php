<?php

require_once('../../init/common.php');

class TMobileTest extends \PHPUnit_Framework_TestCase {
    
    private $scraper = null;
    
    public function __construct() {
        $this->scraper = new \Scrapers\T_Mobile(new GuzzleHttp\Client());
    }
    
    public function test_interpretResponseMessage() {
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_blacklisted.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('blacklisted', $result['status'], 'Unexpected interpretation');
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_empty_form.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('incompatible', $result['status'], 'Unexpected interpretation');
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_clean.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('clean', $result['status'], 'Unexpected interpretation');
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_lost.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('lost', $result['status'], 'Unexpected interpretation');
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_stolen.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('stolen', $result['status'], 'Unexpected interpretation');
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_financed.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('financed', $result['status'], 'Unexpected interpretation');
        
        $response = file_get_contents(FS_ROOT . '/application/tests/tmobile/response_incompatible.html');
        $result = $this->scraper->interpretResponseMessage($response);
        $this->assertEquals('incompatible', $result['status'], 'Unexpected interpretation');

    }
    
    public function test_process_clean() {
        //Clean
        $esn = '013441007198055';
        $this->assertEquals('clean', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        //Clean
        $esn = '357518055291645';
        $this->assertEquals('clean', $this->scraper->process($esn)['status'], 'Unexpected status');
    }
    
    public function test_process_blacklisted() {
        //Blacklisted
        $esn = '355537053337715';
        $this->assertEquals('blacklisted', $this->scraper->process($esn)['status'], 'Unexpected status');
    }
    
    public function test_process_lost_stolen() {
        //Lost/stolen
        $esn = '357518058534660';
        $this->assertEquals('lost', $this->scraper->process($esn)['status'], 'Unexpected status');
    }

    public function test_process_stolen() {
        //Stolen
        $esn = '354439054250982';
        $this->assertEquals('stolen', $this->scraper->process($esn)['status'], 'Unexpected status');
    }
    
    public function test_process_financed() {
        //Financed
        $esn = '359128061252769';
        $this->assertEquals('financed', $this->scraper->process($esn)['status'], 'Unexpected status');
    }
    
    public function test_process_incompatible() {
        
        //Incompatible
        $esn = '990003450167263';
        $this->assertEquals('incompatible', $this->scraper->process($esn)['status'], 'Unexpected status');
    }
}
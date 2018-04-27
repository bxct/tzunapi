<?php

require_once('../../init/common.php');

class VerizonTest extends \PHPUnit_Framework_TestCase {
    
    private $scraper = null;
    
    public function __construct() {
        $this->scraper = new \Scrapers\Verizon(new GuzzleHttp\Client());
    }
    
    public function test_process() {
        $esn = '990000672152073';
        $this->assertEquals('clean', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        $esn = '990004531453839';
        $this->assertEquals('clean', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        $esn = '990004508790429';
        $this->assertEquals('clean', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        $esn = '990000645779903';
        $this->assertEquals('lost_stolen', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        $esn = '990003450167263';
        $this->assertEquals('blacklisted', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        $esn = '990004382148926';
        $this->assertEquals('lost_stolen', $this->scraper->process($esn)['status'], 'Unexpected status');
        
        $esn = '99000466686202';
        $this->assertEquals('incompatible', $this->scraper->process($esn)['status'], 'Unexpected status');
    }
    
    public function test_interpretResponseMessage() {
        
        //Unknown exception message (success??)
        $response = "throw 'allowScriptTagRemoting is false.';
//#DWR-INSERT
//#DWR-REPLY
var s0=[];
dwr.engine._remoteHandleCallback('4','0',{cartIndex:null,errorMessage:null,errorMsgJSON:null,errorType:null,featureId:null,featuresRestored:false,htmlString:null,nextStep:\"dataException\",planName:null,productString:null,readyForCheckout:false,reloadFeatureLines:s0,result:false});";
        $this->assertEquals('clean', $this->scraper->interpretResponseMessage($response)['status'], 'Unexpected interpretation');
        
        //Success exception message
        $response = "throw 'allowScriptTagRemoting is false.';
//#DWR-INSERT
//#DWR-REPLY
var s0=[];
dwr.engine._remoteHandleCallback('4','0',{cartIndex:null,errorMessage:\"It appears you would like to activate your recently purchased device. Please sign in to My Verizon to complete your new device activation<a href=\"https://myaccount.verizonwireless.com/clp/login?redirect=%2Fvzw%2Fsecure%2Fservices%2FactivatePhone.action\"> Activate My New Device</a>\",errorMsgJSON:null,errorType:null,featureId:null,featuresRestored:false,htmlString:null,nextStep:\"validationException\",planName:null,productString:null,readyForCheckout:false,reloadFeatureLines:s0,result:false});";
        $this->assertEquals('clean', $this->scraper->interpretResponseMessage($response)['status'], 'Unexpected interpretation');

        //Lost/stolen exception
        $response = "throw 'allowScriptTagRemoting is false.';
//#DWR-INSERT
//#DWR-REPLY
var s0=[];
dwr.engine._remoteHandleCallback('4','0',{cartIndex:null,errorMessage:\"This device has previously been reported as lost or stolen and cannot be activated at this time.\",errorMsgJSON:null,errorType:null,featureId:null,featuresRestored:false,htmlString:null,nextStep:\"validationException\",planName:null,productString:null,readyForCheckout:false,reloadFeatureLines:s0,result:false});";
        $this->assertEquals('lost_stolen', $this->scraper->interpretResponseMessage($response)['status'], 'Unexpected interpretation');
        
        //Balance exception
        $response = "throw 'allowScriptTagRemoting is false.';
//#DWR-INSERT
//#DWR-REPLY
var s0=[];
dwr.engine._remoteHandleCallback('4','0',{cartIndex:null,errorMessage:\"The phone associated with the Device ID you entered cannot be activated at this time. Please contact Customer Service at 800-922-0204 for assistance.\",errorMsgJSON:null,errorType:null,featureId:null,featuresRestored:false,htmlString:null,nextStep:\"validationException\",planName:null,productString:null,readyForCheckout:false,reloadFeatureLines:s0,result:false});";
        $this->assertEquals('blacklisted', $this->scraper->interpretResponseMessage($response)['status'], 'Unexpected interpretation');
        
        //Lost/stolen
        $response = "throw 'allowScriptTagRemoting is false.';
//#DWR-INSERT
//#DWR-REPLY
var s0=[];
dwr.engine._remoteHandleCallback('4','0',{cartIndex:null,errorMessage:\"This device has previously been reported as lost or stolen and cannot be activated at this time.\",errorMsgJSON:null,errorType:null,featureId:null,featuresRestored:false,htmlString:null,nextStep:\"validationException\",planName:null,productString:null,readyForCheckout:false,reloadFeatureLines:s0,result:false});";
        $this->assertEquals('lost_stolen', $this->scraper->interpretResponseMessage($response)['status'], 'Unexpected interpretation');
        
        //Incompatible
        $response = "throw 'allowScriptTagRemoting is false.';
//#DWR-INSERT
//#DWR-REPLY
var s0=[];
dwr.engine._remoteHandleCallback('4','0',{cartIndex:null,errorMessage:\"The phone associated with the Device ID you entered is not compatible with the Verizon Wireless network.\",errorMsgJSON:null,errorType:null,featureId:null,featuresRestored:false,htmlString:null,nextStep:\"validationException\",planName:null,productString:null,readyForCheckout:false,reloadFeatureLines:s0,result:false});";
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($response)['status'], 'Unexpected interpretation');
        
    }
}
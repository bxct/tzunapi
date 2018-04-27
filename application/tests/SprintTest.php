<?php

require_once('../../init/common.php');

class SprintTest extends \PHPUnit_Framework_TestCase {
    
    private $scraper = null;
    
    public function __construct() {
        $this->scraper = new \Scrapers\Sprint(new GuzzleHttp\Client());
    }
    
    public function test_process() {
        
        //Verizon Good
        $esn = '990000672152073';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        $esn = '990004531453839';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        $esn = '990004508790429';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        //Verizon bad
        //Lost/stolen
        $esn = '990000645779903';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        //Balance
        $esn = '990003450167263';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        //Lost/stolen
        $esn = '990004382148926';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        //T-Mobile Good
        $esn = '013441007198055';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        $esn = '357518055291645';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        //T-Mobile bad
        //Blocked
        $esn = '355537053337715';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        //Lost/Stolen
        $esn = '357518058534660';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        //Financed
        $esn = '357518058538760';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        //AT&T Good
        $esn = '013331004558909';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        $esn = '013439005665737';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        //AT&T Bad
        //Lost/stolen
        $esn = '354439054250982';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        //Lost/stolen
        $esn = '013334008033845';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        
        //Sprint good
        $esn = 'A0000039305583';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('clean', $r, 'Unexpected status');
        
        $esn = '99000450538807';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('clean', $r, 'Unexpected status');
        
        //Sprint
        //Bad
        $esn = '99000279264835';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('lost_stolen', $r, 'Unexpected status');
        //Bad
        $esn = '35853405133799';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
        //Bad
        $esn = '35799605234670';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('financed', $r, 'Unexpected status');
        //Balanced
        $esn = '99000466686202';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('balance', $r, 'Unexpected status');
        //Balanced
        $esn = '352069067624691';
        $r = $this->scraper->process($esn)['status'];
        $this->assertEquals('incompatible', $r, 'Unexpected status');
    }
    
    public function test_interpretResponseMessage() {
        
        /**
         * Verizon
         */
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        ///////
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        /**
         * T-Mobile
         */
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        /**
         * AT&T
         */
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        /**
         * Sprint
         */
        
        $jsonResponse = '{
        "mess":"ESN is clean and can be activated", "ok":true
                ,"sp": {"availabilityTypeCode":1,"availabilityTypeCodeSpecified":false,"notAvailableReasonCode":"0","validationMessage":"Device is valid and cleared for use","manufacturerName":"SAMSUNG","modelName":"SAMSUNG D710 WHITE KIT","freqMode":"C","equipmentFreqTypeCode":"B","modelNumber":"SPHD710WTS","deviceSerialNumber":"A0000039305583","deviceType":0,"deviceTypeSpecified":true,"macId":"D487D8DE1BA2","iccId":null,"imsi":null,"uiccSku":null,"uiccAvailabilityCode":0,"uiccAvailabilityCodeSpecified":false,"uiccNotAvailableReasonCode":0,"uiccNotAvailableReasonCodeSpecified":false,"uiccCompatibility":null,"uiccType":null}
        }';
        $this->assertEquals('clean', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN is clean and can be activated", "ok":true
                ,"sp": {"availabilityTypeCode":1,"availabilityTypeCodeSpecified":false,"notAvailableReasonCode":"0","validationMessage":"Device is valid and cleared for use","manufacturerName":"SAMSUNG","modelName":"SAMSUNG G900 BK XCVR SGL","freqMode":"C","equipmentFreqTypeCode":"H","modelNumber":"SPHG900BK1","deviceSerialNumber":"99000450538807","deviceType":2,"deviceTypeSpecified":true,"macId":null,"iccId":"89011200000313624750","imsi":"310120031362475","uiccSku":"CZ2102LWR","uiccAvailabilityCode":1,"uiccAvailabilityCodeSpecified":false,"uiccNotAvailableReasonCode":0,"uiccNotAvailableReasonCodeSpecified":true,"uiccCompatibility":"Y","uiccType":"U"}
        }';
        $this->assertEquals('clean', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. Device is either STOLEN or LOST"
                ,"sp": {"availabilityTypeCode":0,"availabilityTypeCodeSpecified":false,"notAvailableReasonCode":"1","validationMessage":"Device is either STOLEN or LOST","manufacturerName":"APPLE, INC","modelName":"INS IPHONE 5 BL 16GB SGL","freqMode":"C","equipmentFreqTypeCode":"G","modelNumber":"RECINS516B1","deviceSerialNumber":"99000279264835","deviceType":2,"deviceTypeSpecified":true,"macId":null,"iccId":"89011200000434900774","imsi":"310120043490077","uiccSku":"CZ2104LWR","uiccAvailabilityCode":0,"uiccAvailabilityCodeSpecified":false,"uiccNotAvailableReasonCode":1,"uiccNotAvailableReasonCodeSpecified":true,"uiccCompatibility":"Y","uiccType":"U"}
        }';
        $this->assertEquals('lost_stolen', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. Device is FRAUDULENT"
                ,"sp": {"availabilityTypeCode":0,"availabilityTypeCodeSpecified":false,"notAvailableReasonCode":"3","validationMessage":"Device is FRAUDULENT","manufacturerName":"APPLE, INC","modelName":"IPHONE 5C PINK 32GB SGL","freqMode":"C","equipmentFreqTypeCode":"G","modelNumber":"IPH5C32GPK1","deviceSerialNumber":"35799605234670","deviceType":2,"deviceTypeSpecified":true,"macId":null,"iccId":"89011201000016813261","imsi":"310120101681326","uiccSku":"CZ2104LWC","uiccAvailabilityCode":0,"uiccAvailabilityCodeSpecified":false,"uiccNotAvailableReasonCode":3,"uiccNotAvailableReasonCodeSpecified":true,"uiccCompatibility":"Y","uiccType":"C"}
        }';
        $this->assertEquals('financed', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. Device is in use"
                ,"sp": {"availabilityTypeCode":0,"availabilityTypeCodeSpecified":false,"notAvailableReasonCode":"2","validationMessage":"Device is in use","manufacturerName":"SAMSUNG","modelName":"SAMSUNG G900 WHT XCVR SGL","freqMode":"C","equipmentFreqTypeCode":"H","modelNumber":"SPHG900WT1","deviceSerialNumber":"99000466686202","deviceType":2,"deviceTypeSpecified":true,"macId":null,"iccId":"89011200000321882192","imsi":"310120032188219","uiccSku":"CZ2102LWR","uiccAvailabilityCode":0,"uiccAvailabilityCodeSpecified":false,"uiccNotAvailableReasonCode":2,"uiccNotAvailableReasonCodeSpecified":true,"uiccCompatibility":"Y","uiccType":"U"}
        }';
        $this->assertEquals('balance', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
        
        $jsonResponse = '{
        "mess":"ESN can\'t be activated. "
                ,"sp": {"Type":"Soap Exception","Detail":{"ns1:errorDetailItem":{"@xmlns:ns1":"http://integration.sprint.com/common/ErrorDetailsV2.xsd","ns1:providerError":[{"ns1:providerErrorCode":"704","ns1:providerErrorText":"Server.704:NMS returned status_code=38, status_text=DOES_NOT_EXIST_IN_DB: Device does not exists in DB"},{"ns1:providerErrorCode":"Server.704","ns1:providerErrorText":"nms_exception","ns1:errorSystem":"NMS"}]}},"ErrorCode":"Server.704","Error":"nms_exception"}
        }';
        /**
         * @todo Make checking more precise
         * $this->assertEquals('financed', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
         */
        $this->assertEquals('incompatible', $this->scraper->interpretResponseMessage($jsonResponse)['status'], 'Unexpected interpretation');
    }
}
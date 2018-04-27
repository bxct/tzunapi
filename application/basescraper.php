<?php

use \GuzzleHttp\Client;

/**
 * 
 */
class BaseScraper implements \Interfaces\Scraper {
    
    protected $_isGSMA = true;
    
    private $gsmaApiUrl = '';
    
    private $gsmaApiKey = '';
    
    private $gsmaUsername = '';    
    
    private $gsmaError;
    
    private $gsmaClient;
    
    private $gsmaDebug = false;
    
    private $gsmaResponse = null;
    
    protected $client = null;
    
    public function __construct($client) {
        //Dependency injection approach for Client
        $this->client = $client;
    }
    
    public function isGSMA(){
        return $this->_isGSMA;
    }
    
    /**
     * Get data with GSMA
     * @param string $imei
     * @return array
     */     
    public function process($imei) {
       
        $this->gsmaClient = $this->getGSMAClient();       
         
        $result = $this->getMobileInfo($imei);

        if (!$result) {
            return array(
                'status' => 'failed',
                'status_details' => $this->getGSMAError()
            );
        }

        return $this->interpretGSMAEResponseMessage($result);
    }
    
    /**
     * Randomizes the User-Agent header string based on different known browsers, operating system, browser version etc
     * 
     * @param integer $number pre-defined key of string to use out of composed array of combinations
     * @return string
     */
    protected function getUserAgentString($number = false) {
        
        /**
         * @todo
         * Randomize the string by operating system
         */
        //1. Randomize the string by browser type, browser version and operating system version
        $userAgent[] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_' . rand(8,10) . '_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/4' . rand(1,2) . '.0.2311.152 Safari/537.36'; //My Chrome
        $userAgent[] = 'User-Agent	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_' . rand(8,10) . '_3) AppleWebKit/600.6.3 (KHTML, like Gecko) Version/' . rand(7,8) . '.0.6 Safari/600.6.3'; //My Safari
        $userAgent[] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.' . rand(8,10) . '; rv:38.0) Gecko/20100101 Firefox/3' . rand(6,8) . '.0';
        return $userAgent[$number!==false?$number:(rand(0, count($userAgent)-1))];
    }
    
    /**
     * Provides randomized zip code
     * 
     * @param integer $number
     * @return string
     */
    protected function getSomeZipCode($number = false) {
        $availableZipCodes = array(
            //Commack, NY
            array('11975', 'Commack', 'NY'),
            //Hempstead, NY
            array('11549', 'Hempstead', 'NY'),
            array('11550', 'Hempstead', 'NY'),
            array('11551', 'Hempstead', 'NY'),
            //Smithtown, NY
            array('11787', 'Smithtown', 'NY'),
            array('11788', 'Smithtown', 'NY'),
            //Deadwood, SD
            array('57732', 'Deadwood', 'SD'),
            //Fargo, ND
            array('58102', 'Fargo', 'ND'),
            array('58103', 'Fargo', 'ND'),
            array('58104', 'Fargo', 'ND'),
            array('58105', 'Fargo', 'ND'),
            array('58106', 'Fargo', 'ND'),
            array('58107', 'Fargo', 'ND'),
            array('58108', 'Fargo', 'ND'),
            array('58109', 'Fargo', 'ND'),
            array('58121', 'Fargo', 'ND'),
            array('58122', 'Fargo', 'ND'),
            array('58123', 'Fargo', 'ND'),
            array('58124', 'Fargo', 'ND'),
            array('58125', 'Fargo', 'ND'),
            array('58126', 'Fargo', 'ND'),
            //Brainerd, MN
            array('56401', 'Brainerd', 'MN'),
            array('56425', 'Brainerd', 'MN')
        );
        
        return $availableZipCodes[$number!==false?$number:(rand(0, count($availableZipCodes)-1))];
    }
   
    protected  function getGSMAClient() {
        
        if(empty($this->gsmaClient)) {
            $this->gsmaClient = new Client();
        }
        
        return $this->gsmaClient;
    }
    
    protected function getMobileInfo ( $imei ) {
        
        if(empty($this->gsmaClient)) {
            return false;
        }        
        
//        if(!($this->_validateIMEI($imei) || $this->_validateESN($imei) || $this->_validateMEID($imei))) {   
//            return false;
//        }
        
        return $this->_callGSMA('detailedblwithmodelinfo', array(
            'imeinumber' => $imei
        ));
    }
    
    protected function getMobileInfoByESN ( $esn ) {
        
        if(empty($this->gsmaClient)) {
            return false;
        }
        
        if(!$this->_validateESN($esn)) {            
            return false;
        }
        
        return $this->_callGSMA('detailedblwithmodelinfo', array(
            'imeiNumber' => $esn
        ));
    }
    
    protected function getMobileInfoByMEID ( $meid ) {
        
        if(empty($this->gsmaClient)) {
            return false;
        }
        
        if(!$this->_validateMEID($meid)) {            
            return false;
        }
        
        return $this->_callGSMA('detailedblwithmodelinfo', array(
            'imeiNumber' => $meid
        ));
     }
    
    protected function getGSMAError() {
        
        return $this->gsmaError;        
    }   
     
    protected function interpretGSMAEResponseMessage($result) {

        $response = array(
            'status' => 'failed',
            'status_details' => 'could not parse response'
        );

        if (isset($result['blackliststatus']) && isset($result['greyliststatus'])) {
            $response['status_details'] = $result;
            
            if ($result['blackliststatus'] == 'Yes') {
                $response['status'] = 'blacklisted';
                //$response['status_details'] = $this->gsmaClient->getHistory();
            } else if ($result['greyliststatus'] == 'Yes') {
                $response['status'] = 'greylisted';
                //$response['status_details'] = $this->gsmaClient->getHistory();
            } else {
                $response['status'] = 'clean';
                //$response['status_details'] = $this->gsmaClient->getHistory();
            }
        }

        return $response;
    }
        
    private function _validateIMEI( $imei ) {
        $length = strlen($imei);
        if(($length == '14' ||  $length == '15') && preg_match('/[0-9]+/', $imei)) {
            return true;
        }
       
        $this->gsmaError = 'IMEI is not valid';
        return false;
    }
    
    private function _validateESN( $esn ) {
        $length = strlen($esn);
        if($length == '8' && preg_match('/^[A-Za-z]/', $esn)) {
            return true;
        }
        
        if($length == '11' && preg_match('/[0-9]+/', $esn)) {
            return true;
        }
        
        $this->gsmaError = 'ESN is not valid';
        return false;
    }
        
    private function _validateMEID( $meid ) {
        $length = strlen($meid);
        if($length == '18' ||  $length == '19') {
            return true;
        }
       
        $this->gsmaError = 'MEID is not valid';
        return false;
    }    
    
    private function _callGSMA ( $operation, $data ) {
       
        $response = false;
        
        $post = array_merge(array(
            'username' => $this->gsmaUsername,
            'apikey' => $this->gsmaApiKey,
        ), $data);
 

        $arrayResponse = false;
        
        try {
            $response = $this->gsmaClient->post( $this->gsmaApiUrl.$operation , [
                'debug' => $this->gsmaDebug,
                'body' => $post
            ]);
         
            $content = $response->getBody()->getContents();
            $arrayResponse = json_decode($content, TRUE);
            if(empty($arrayResponse) || !is_array($arrayResponse)) {
                //Response is not json, check if it is XML
                $xml = simplexml_load_string($content);
                if(!empty($xml)) {
                    foreach($xml as $k => $v){
                        $arrayResponse[$k] = $v; 
                    }
                }
                
            }
//            
//            $jsonResponse = $response->json();            
//            if(!empty($jsonResponse) && !is_array($jsonResponse)) {
//                $arrayResponse = json_decode($jsonResponse, TRUE);
//            }
        }  catch (Exception $e) {
            $this->gsmaError  = $e->getRequest() . "\n";
            if ($e->hasResponse()) {
                $this->gsmaError .=  $e->getResponse();
            }
            return FALSE;
        }         
        if(empty($arrayResponse) || !is_array($arrayResponse)) {
            $this->gsmaError  = 'Error getting response from API';
            return FALSE;
        }

        if($arrayResponse['responsestatus'] != 'success') {
            $this->gsmaError  = 'Error code: '.$arrayResponse['errorcode'].'. '.$arrayResponse['errordesc'];
            return FALSE;
        }
        
        $this->gsmaResponse = $arrayResponse;
        
        return $this->gsmaResponse;
    }
    
    public function getHistory ( $returnLast  = false ) {
        $details = '';
        if(isset($this->gsmaResponse['imeihistory']) && !empty($this->gsmaResponse['imeihistory'])) {
            if($returnLast) {
                if($this->gsmaResponse['imeihistory'][0]['action'] != 'NA') {
                    $details = $this->gsmaResponse['imeihistory'][0]['action'].' '. $this->gsmaResponse['imeihistory'][0]['date'].' by '.$this->response['imeihistory'][0]['by'];
                }                
                
            } else {
                
                $details = array();
                foreach($this->gsmaResponse['imeihistory'] as $h) {
                    if($h['action'] != 'NA') {
                        $details[] = $h['action'].' '. $h['date'].' by '.$h['by'];
                    }                    
                }
                $details = implode(';', $details);                
            }             
        }
        return $details;
    }
    
    public function getDeviceTitle() {
        return (isset($this->gsmaResponse['marketingname']) && $this->gsmaResponse['marketingname'] !='NA')?$this->gsmaResponse['marketingname']:'';
    }
}

<?php

namespace Scrapers;

class Verizon extends \BaseScraper {
    
    protected $_isGSMA = false;
    
    /**
     * Runs necessary request agains Verizon website ESN checking form and interprets the response
     * 
     * @param string $esn
     * 
     * @return array
     */
    public function process($esn) {
        
        $userAgent = $this->getUserAgentString();
        $zipCode = $this->getSomeZipCode();
        
        /**
         * 1. Submit form data (ZIP code) POST http://www.verizonwireless.com/b2c/vzwfly
         */
        $setCookies = array();
        try {
            //Make call to retrieve Set-Cookie header
            $verizonResponse = $this->client->post('http://www.verizonwireless.com/b2c/vzwfly', [
                'headers' => [
                    'Origin' => 'http://www.verizonwireless.com',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6,uk;q=0.4',
                    'User-Agent' => $userAgent,
                    'Content-Type' => 'text/plain',
                    'Accept' => '*/*',
                    'Referer' => 'http://www.verizonwireless.com/b2c/nso/enterDeviceId.do?&&zipRdr=y',
                    'Connection' => 'keep-alive'
                ],
                'body' => [
                    'fd' => '',
                    'go' => '/nso/enterDeviceId.do',
                    'atg' => '',
                    'zipcode' => $zipCode[0],
                    'rememberMyZip' => '',
                    'state' => '',
                    'prevstate' => '',
                    'change' => '',
                    'filter' => '',
                ],
                'allow_redirects' => false,
                'timeout' => 20
            ]);
            if ($verizonResponseHeaders = $verizonResponse->getHeaders()) {
                if (array_key_exists('Set-Cookie', $verizonResponseHeaders)) {
                    $setCookies = $verizonResponseHeaders['Set-Cookie'];
                }
            }
        } catch (\Exception $e) {
            \Logger::write($e->getMessage(), 'custom', 'errors_' . array_slice(array_reverse(explode('\\', __CLASS__)),0,1)[0]);
            echo $e->getRequest() . "\n";
            if ($e->hasResponse()) {
                echo $e->getResponse() . "\n";
            }
        }
        
        $requestCookies = array();
        foreach($setCookies as &$setCookie){
            $newCookie = trim(explode(';', $setCookie)[0]);
            $newCookieName = trim(explode('=', $newCookie)[0]);
            $requestCookies[$newCookieName] = $newCookie;
        }
        
        $scriptStarted = time() . sprintf('%03d', rand(0,2000));
        
        //Collecting all possible cookies
        $additionalCookiesPrepared = array(
            'SESSION_VALUE' => 'SESSION_VALUE=' . explode('=', $requestCookies['JSESSIONIDB2C'])[1] . '!' . $scriptStarted,
//            'TIME_CHECKER' => 'TIME_CHECKER=' . ($scriptStarted + rand(1,10)),
            //Unauthorized cookies
//            'ZIP_CONFIRMED' => 'ZIP_CONFIRMED=false',
//            'ZIPCODE' => 'ZIPCODE=92618',
//            'CITY' => 'CITY=Irvine',
            'ZIPCODE' => 'ZIPCODE=' . $zipCode[0],
            'CITY' => 'CITY=' . $zipCode[1],
            'STATE' => 'STATE=' . $zipCode[2],
            'chkcookie' => 'chkcookie=' . ($scriptStarted + rand(1000,5000)),
            'MP_LANG' => 'MP_LANG=en',
            'javascriptEnabled' => 'javascriptEnabled=true',
            
        );

        $crc = $additionalCookiesPrepared;
        
        //Store and use all received cookies
        foreach($requestCookies as $k => $v) {
            $crc[$k] = $v;
        }
        
        /** 
         * 2. Retrieve the engine.js file to get scriptSessionId
         */
        $cookieString = $crc['GLOBALID'] . '; ZIPCODE=' . $zipCode[0] . '; CITY=' . $zipCode[1] . '; STATE=' . $zipCode[2] . '; ' . $crc['NSC_xxx_hwt'] . '; MP_LANG=en; ' . $crc['JSESSIONIDB2C'] . '; ' . $crc['SESSION_VALUE'] . '; ' . $crc['B2CP'] . ';';
        
        $engineJSBody = '';
        try {
            //Make to get JS file
            $verizonResponse = $this->client->get('http://www.verizonwireless.com/b2c/dwr/engine.js', [
                'headers' => [
                    'Origin' => 'http://www.verizonwireless.com',
                    'Accept-Encoding' => 'gzip, deflate, sdch',
                    'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6,uk;q=0.4',
                    'User-Agent' => $userAgent,
                    'Content-Type' => 'text/plain',
                    'Accept' => '*/*',
                    'Referer' => 'http://www.verizonwireless.com/b2c/nso/enterDeviceId.do?zipRdr=y',
                    'Connection' => 'keep-alive',
                    'Cookie' => $cookieString,
                ],
                'allow_redirects' => false,
                'timeout' => 20
            ]);
            if ($verizonResponse->getBody()) {
                if ($verizonResponseHeaders = $verizonResponse->getHeaders()) {
                    if (array_key_exists('Set-Cookie', $verizonResponseHeaders)) {
                        $setEngineJSCookies = $verizonResponseHeaders['Set-Cookie'];
                    }
                }
                $verizonResponse = $verizonResponse->getBody();
            }
            $engineJSBody = (string) $verizonResponse;
        } catch (RequestException $e) {
            echo $e->getRequest() . "\n";
            if ($e->hasResponse()) {
                echo $e->getResponse() . "\n";
            }
        }
        
        /**
         * 3. Retrieve status data using all collected Cookies etc
         */
        if($engineJSBody) {
            /**
            * Parse out the _origScriptSessionId
            *  => from www.verizonwireless.com/b2c/dwr/engine.js + few weird numbers 
            * (hehe... dwr.engine._scriptSessionId = dwr.engine._origScriptSessionId + Math.floor(Math.random() * 1000))
            */
            if(preg_match('/\_origScriptSessionId\s=\s\"([A-Z0-9]+)\"/', $engineJSBody, $sessionIDMatches)) {
                if(array_key_exists(1, $sessionIDMatches) && $sessionIDMatches[1]) {
                    $body = array(
                        'callCount' => 'callCount=1',
                        'page' => 'page=/b2c/nso/enterDeviceId.do?&&zipRdr=y',
                        'httpSessionId' => 'httpSessionId=' . explode('=', $crc['JSESSIONIDB2C'])[1],
                        'scriptSessionId' => 'scriptSessionId=' . $sessionIDMatches[1] . sprintf('%03d', rand(1,999)),
                        'c0-scriptName' => 'c0-scriptName=nsoAjaxService',
                        'c0-methodName' => 'c0-methodName=validateDevice',
                        'c0-id' => 'c0-id=0',
                        'c0-param0' => 'c0-param0=string:' . $esn,
                        'c0-param1' => 'c0-param1=null:null',
                        'batchId' => 'batchId=2',
                    );
                    
                    $headers = [
                        'Host' => 'www.verizonwireless.com',
                        'Origin' => 'http://www.verizonwireless.com',
                        'Accept-Encoding' => 'gzip, deflate',
                        'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6,uk;q=0.4',
                        'User-Agent' => $userAgent,
                        'Content-Type' => 'text/plain',
                        'Accept' => '*/*',
                        'Referer' => 'http://www.verizonwireless.com/b2c/nso/enterDeviceId.do?&&zipRdr=y',
                        'Connection' => 'keep-alive' ,
                        'Cookie' => $cookieString,
                    ];
                    
                    //Cookie that works
                    $headers['Cookie'] = $cookieString;
                    
                    //curl 'http://www.verizonwireless.com/b2c/dwr/call/plaincall/nsoAjaxService.validateDevice.dwr' -H 'Origin: http://www.verizonwireless.com' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: en-US,en;q=0.8,ru;q=0.6,uk;q=0.4' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36' -H 'Content-Type: text/plain' -H 'Accept: */*' -H 'Referer: http://www.verizonwireless.com/b2c/nso/enterDeviceId.do?&&zipRdr=y' -H $'Cookie: GLOBALID=1k0TYIfDUzUCfcJqkKviSsLWcKkK%2F2ayJN3UuGyNlUH1C1a2KwtNvMOBRPxKPZNW; TIME_CHECKER=1432596794191; ZIP_CONFIRMED=false; ZIPCODE=92618; CITY=Irvine; STATE=CA; NSC_xxx_hwt=ffffffffa17b0cd945525d5f4f58455e445a4a420000; __g_u=114400344951815_0; __g_c=c%3A114400344951815%7Cd%3A0%7Ca%3A0; MP_LANG=en; NSC_xxx_iq_mcwt=ffffffff09f7174045525d5f4f58455e445a4a422241; JSESSIONIDB2C=0Lx2Vl2b9JQQy6w25nCTxLK17JB9dm3H4h1ln1CdPLZShGnjf2rx!1198738123!cis-wapp13!5102!-1; SESSION_VALUE=0Lx2Vl2b9JQQy6w25nCTxLK17JB9dm3H4h1ln1CdPLZShGnjf2rx!1198738123!cis-wapp13!5102!-1!1432731195586; JSESSIONIDHP=1PSVbxuG0Lyj8-8-JMBsN3VA3EJYaLwhFAKC5Xn1cSsr43MIpZXf!1006405630!cis-wapp13!5233!-1; javascriptEnabled=true; CARTVIEW=FALSE; mbox=PC#1432596795475-867012.26_10#1440507202|session#1432731197244-390335#1432733062; invodoViewer=CeEX2bOOwbDc0dDUsmUUw0ALJ-40Q0Y0fp-hfW_MhWtN; invodoVisitor=CMpuKfcXmfN65bNqwCcqn50; 44153975-VID=112840384791521; 44153975-SKEY=6079633769778071012; HumanClickSiteContainerID_44153975=Secondary6; B2CP=45580; s_vi=[CS]v1|2AB1D89E85013EEA-60000114600085C1[CE]; s_pers=%20s_lastvisit%3D1432731198909%7C1527339198909%3B%20s_nr5%3D1432731594972-Repeat%7C1464267594972%3B; s_sess=%20s_cc%3Dtrue%3B%20ppv_o%3D%252Fstore%252Fnso%252Fadd%2520new%2520device%252Fcheck%2520device%2520eligibility%2520error%3B%20s_ppv%3D%252Fstore%252Fnso%252Fadd%252520new%252520device%252Fcheck%252520device%252520eligibility%252C71%252C19%252C1699%3B%20s_sq%3Dvzwiglobal%252Cvzwiconsumer%253D%252526pid%25253D%2525252Fstore%2525252Fnso%2525252Fadd%25252520new%25252520device%2525252Fcheck%25252520device%25252520eligibility%25252520error%252526pidt%25253D1%252526oid%25253Dfunctiononclick(event)%2525257BvzwSc.trackLink(\'CheckDevice\')%2525253B%2525257D%252526oidt%25253D2%252526ot%25253DSUBMIT%3B' -H 'Connection: keep-alive' --data-binary $'callCount=1\npage=/b2c/nso/enterDeviceId.do?&&zipRdr=y\nhttpSessionId=0Lx2Vl2b9JQQy6w25nCTxLK17JB9dm3H4h1ln1CdPLZShGnjf2rx!1198738123!cis-wapp13!5102!-1\nscriptSessionId=412B528D8908C58C6DA5480696DF0886698\nc0-scriptName=nsoAjaxService\nc0-methodName=validateDevice\nc0-id=0\nc0-param0=string:990004531453839\nc0-param1=null:null\nbatchId=2\n' --compressed
                    $statusResponseString = '';
                    try {
                        //Make call for ESN check
                        $verizonResponse = $this->client->post('http://www.verizonwireless.com/b2c/dwr/call/plaincall/nsoAjaxService.validateDevice.dwr', [
                            'headers' => $headers,
                            'body' => implode("\n", $body),
                            'allow_redirects' => false,
                            'timeout' => 20
                        ]);
                        if ($verizonResponse->getBody()) {
                            if ($verizonResponseHeaders = $verizonResponse->getHeaders()) {
                                if (array_key_exists('Set-Cookie', $verizonResponseHeaders)) {
                                    $setVerificationCookies = $verizonResponseHeaders['Set-Cookie'];
                                }
                            }
                            $verizonResponse = $verizonResponse->getBody();
                        }
                        $statusResponseString = (string) $verizonResponse;
                    } catch (RequestException $e) {
                        echo $e->getRequest() . "\n";
                        if ($e->hasResponse()) {
                            echo $e->getResponse() . "\n";
                        }
                    }

                    //Interpret the response received from verizonwireless
                    return $this->interpretResponseMessage($statusResponseString);
                }
            }
        }
        return array(
            'status' => 'failed',
            'status_details' => ''
        );
    }
    
    /**
     * Parses out the status details from the HTTP response of checking script
     * 
     * @param string $messageBody
     * @return array
     */
    public function interpretResponseMessage($messageBody) {
       
        $matches = array();
        $response = array(
            'status' => 'incompatible',
            'status_details' => 'could not parse response'
        );
        
        //Check if response contains JS object with params
        if(preg_match('/\{(.*)\}/', $messageBody, $matches)) {
            
            //Compose array of response params
            $responseParametersNamed = array();
            foreach(($responseParameters = array_map(function($el){return explode(':', $el);}, explode(',', $matches[1]))) as $k => $rp) {
                $responseParametersNamed[array_shift($rp)] = implode(':', $rp);
            }
            
            //Retrieve complete message
            if(array_key_exists('errorMessage', $responseParametersNamed)) {
                $response['status_details'] = $responseParametersNamed['errorMessage']!=='null'?$responseParametersNamed['errorMessage']:'';
            }
            
            /**
             * Detect the status slug based on error message
             */
            switch(true) {
                
                case array_key_exists('errorMessage', $responseParametersNamed) 
                        && !empty($responseParametersNamed['errorMessage']) 
                        && preg_match('/recently\spurchased\sdevice/', $responseParametersNamed['errorMessage']):
                    $response['status'] = 'clean';
                    break;
                
                case !empty($responseParametersNamed['errorMessage']) && ($responseParametersNamed['errorMessage'] == 'null'):
                    $response['status'] = 'clean';
                    $response['status_details'] = array(
                        'regular_status_details' => 'Assumed clean',
                        'more_status_details' => $messageBody
                    );
                    break;
                
                case array_key_exists('errorMessage', $responseParametersNamed)
                        && !empty($responseParametersNamed['result'])
                        && $responseParametersNamed['result'] == 'true':
                    $response['status'] = 'clean';
                    $response['status_details'] = array(
                        'regular_status_details' => 'Assumed clean',
                        'more_status_details' => $messageBody
                    );
                    break;
                
                case array_key_exists('errorMessage', $responseParametersNamed) 
                        && !empty($responseParametersNamed['errorMessage']) 
                        && preg_match('/is\snot\scompatible/', $responseParametersNamed['errorMessage']):
                    $response['status'] = 'incompatible';
                    break;
                    
                case array_key_exists('errorMessage', $responseParametersNamed) 
                        && !empty($responseParametersNamed['errorMessage']) 
                        && preg_match('/reported\sas\slost\sor\sstolen/', $responseParametersNamed['errorMessage']):
                    $response['status'] = 'lost_stolen';
                    break;
                
                case array_key_exists('errorMessage', $responseParametersNamed) 
                        && !empty($responseParametersNamed['errorMessage']) 
                        && preg_match('/cannot\sbe\sactivated\sat\sthis\stime/', $responseParametersNamed['errorMessage']):
                    $response['status'] = 'blacklisted';
                    break;
                
                default:
                    break;
            }
        }
        
        return  $response;
    }
}

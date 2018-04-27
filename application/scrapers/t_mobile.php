<?php

namespace Scrapers;

class T_Mobile extends \BaseScraper {
    
    protected $_isGSMA = false;
    
    /**
     * Runs necessary request agains T-Mobile website ESN checking form and interprets the response
     * 
     * @param string $esn
     * 
     * @return array
     */
    public function process($esn) {
        
        $userAgent = $this->getUserAgentString();
        $setCookies = array();
        $statusResponseString = '';
        
        /**
         * 1. Retrieve the form page http://www.t-mobile.com/verifyIMEI.aspx
         */
        $tMobileResponse = null;
        try {
            //Make call to retrieve Set-Cookie header
            $tMobileResponse = $this->client->get('http://www.t-mobile.com/verifyIMEI.aspx', [
                'headers' => [
                    'Host' => 'www.t-mobile.com',
                    'Accept-Encoding' => 'gzip, deflate, sdch',
                    'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6,uk;q=0.4',
                    'User-Agent' => $userAgent,
                    'Content-Type' => 'text/plain',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Connection' => 'keep-alive'
                ],
                'allow_redirects' => false,
                'timeout' => 90,
            ]);
            if ($tMobileResponse->getBody()) {
                if ($tMobileResponseHeaders = $tMobileResponse->getHeaders()) {
                    if (array_key_exists('Set-Cookie', $tMobileResponseHeaders)) {
                        $setCookies = $tMobileResponseHeaders['Set-Cookie'];
                    }
                }
                $tMobileResponse = $tMobileResponse->getBody();
            }
        } catch (\Exception $e) {
            \Logger::write($e->getMessage(), 'custom', 'errors_' . array_slice(array_reverse(explode('\\', __CLASS__)),0,1)[0]);
            echo $e->getRequest() . "\n";
            if ($e->hasResponse()) {
                echo $e->getResponse() . "\n";
            }
        }

        if($tMobileResponse) {
            $requestCookies = array();
            foreach($setCookies as &$setCookie){
                $newCookie = trim(explode(';', $setCookie)[0]);
                $newCookieName = trim(explode('=', $newCookie)[0]);
                $requestCookies[$newCookieName] = $newCookie;
            }

            //Collecting all possible cookies
            $additionalCookiesPrepared = array(
                'TMobileVisitIgnored' => 'TMobileVisitIgnored=true' ,
                'TMobileUSLocationDenied' => 'TMobileUSLocationDenied=true',
                'TMobileSpanish=IsSpanishUser' => 'TMobileSpanish=IsSpanishUser=false',
                'MP_LANG' => 'MP_LANG=en',
//                'IRMS_la3290' => 'IRMS_la3290=1432848828619',
//                'bkrid'  => 'bkrid=1443020168',
                '__CT_Data' => '__CT_Data=gpv=1&apv_18_www15=1',
                'WRUID' => 'WRUID=0',
                's_visbtwpur' => 's_visbtwpur=1',
                's_cc' => 's_cc=true',
                'gpv_v10' => 'gpv_v10=www%2Fverifyimei.aspx',
                's_isvisst' => 's_isvisst=1',
                's_v20_persist2' => 's_v20_persist2=-null',
                's_visit' => 's_visit=1'
            );

            $crc = $additionalCookiesPrepared;

            //Store and use all received cookies
            foreach($requestCookies as $k => $v) {
                $crc[$k] = $v;
            }

            $cookieString = $crc['TMobileCommon'] . '; ' . $crc['ASP.NET_SessionId'] . '; TMobileVisitIgnored=true; TMobileUSLocationDenied=true; TMobileSpanish=IsSpanishUser=false; MP_LANG=en; s_dfa=tmobusprod%2Ctmobustmocomprod; __CT_Data=gpv=1&apv_18_www15=1; WRUID=0; s_visbtwpur=1; s_cc=true; gpv_v10=www%2Fverifyimei.aspx; s_isvisst=1; s_v20_persist2=-null; s_visit=1; fsr.s=%7B%22v2%22%3A1%2C%22v1%22%3A1%2C%22rid%22%3A%22de35435-94829304-2365-1814-83cee%22%2C%22cp%22%3A%7B%22section_my_tmobile%22%3A%22N%22%2C%22section_www_tmobile%22%3A%22Y%22%2C%22beta%22%3A%22N%22%2C%22Upgrade%22%3A%22N%22%2C%22Why_TMO%22%3A%22N%22%2C%22Add_a_Line%22%3A%22N%22%2C%22PrePaid%22%3A%22N%22%2C%22PrePaid_Activation%22%3A%22N%22%2C%22isEIP%22%3A%22N%22%2C%22creditClass%22%3A%22N%22%2C%22storeID%22%3A%22N%22%2C%22orderID%22%3A%22N%22%2C%22cartType%22%3A%22N%22%2C%22BYOD%22%3A%22N%22%2C%22SIM%22%3A%22N%22%2C%22Adobe_VID%22%3A%2237504582891295588043148588726903365494%22%2C%22Support_LoggedIn%22%3A%22N%22%7D%2C%22to%22%3A10%2C%22mid%22%3A%22de35435-94829632-1685-9054-2ca95%22%2C%22rt%22%3Afalse%2C%22rc%22%3Afalse%2C%22c%22%3A%22https%3A%2F%2Fwww.t-mobile.com%2FverifyIMEI.aspx%22%2C%22pv%22%3A1%2C%22lc%22%3A%7B%22d13%22%3A%7B%22v%22%3A1%2C%22s%22%3Afalse%7D%7D%2C%22cd%22%3A13%2C%22sd%22%3A13%7D';

            //Form post data
            $data = [
                'imei' => $esn
            ];
            
            //Parse the initial page for post data elements
            //1. __EVENTTARGET
            $data['__EVENTTARGET'] = '';
            if(preg_match('/id\=\"\_\_EVENTTARGET\"\svalue\=\"(.*)\"/', $tMobileResponse, $matches)) {
                if(array_key_exists(1, $matches)) {
                    $data['__EVENTTARGET'] = $matches[1];
                }
            }
            
            //2. __EVENTARGUMENT
            $data['__EVENTARGUMENT'] = '';
            if(preg_match('/id\=\"\_\_EVENTARGUMENT\"\svalue\=\"(.*)\"/', $tMobileResponse, $matches)) {
                if(array_key_exists(1, $matches)) {
                    $data['__EVENTARGUMENT'] = $matches[1];
                }
            }
            
            //3. __VIEWSTATE
            $data['__VIEWSTATE'] = '';
            if(preg_match('/id\=\"\_\_VIEWSTATE\"\svalue\=\"(.*)\"/', $tMobileResponse, $matches)) {
                if(array_key_exists(1, $matches)) {
                    $data['__VIEWSTATE'] = $matches[1];
                }
            }
            
            //4. __EVENTVALIDATION
            $data['__EVENTVALIDATION'] = '';
            if(preg_match('/id\=\"\_\_EVENTVALIDATION\"\svalue\=\"(.*)\"/', $tMobileResponse, $matches)) {
                if(array_key_exists(1, $matches)) {
                    $data['__EVENTVALIDATION'] = $matches[1];
                }
            }
            
            try {
                //Make call to get IMEI status
                $tMobileResponse = $this->client->post('http://www.t-mobile.com/verifyIMEI.aspx', [
                    'body' => '__EVENTTARGET=doPostBack&__EVENTARGUMENT=&__VIEWSTATE=' . urlencode($data['__VIEWSTATE']) . '&__EVENTVALIDATION=' . urlencode($data['__EVENTVALIDATION']) . '&imei=' . $esn,
                    'headers' => [
                        'Origin' => 'http://www.t-mobile.com',
                        'Accept-Encoding' => 'gzip, deflate',
                        'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6,uk;q=0.4',
                        'User-Agent' => $userAgent,
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Cache-Control' => 'max-age=0',
                        'Referer' => 'http://www.t-mobile.com/verifyIMEI.aspx',
                        'Connection' => 'keep-alive',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Cookie' => $cookieString,
                    ],
                    'allow_redirects' => false,
                    'timeout' => 90,
//                        'save_to' => FS_ROOT . '/application/tests/tmobile/response_incompatible.html',
                ]);

                if ($tMobileResponse->getBody()) {
                    $statusResponseString = $tMobileResponse->getBody();
                }
            } catch (\Exception $e) {
                \Logger::write($e->getMessage(), 'custom', 'errors_' . array_slice(array_reverse(explode('\\', __CLASS__)),0,1)[0]);
                echo $e->getRequest() . "\n";
                if ($e->hasResponse()) {
                    echo $e->getResponse() . "\n";
                }
            }
        }
        
        return $this->interpretResponseMessage($statusResponseString);
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

        /**
         * Detect the status parameters based on response page content
         */
        switch (true) {

            case preg_match('/device\sis\sblocked\sand\swill\snot\swork/', $messageBody):
                $response['status'] = 'blacklisted';
                preg_match('/\<h5\sclass\=\"alert\"\>\<strong\>(.*)\<\/strong\>/', $messageBody, $matches);
                if(array_key_exists(1, $matches)) {
                    $response['status_details'] = $matches[1];
                }
                break;
            //Clean
            case preg_match('/Congratulations/i', $messageBody) && preg_match('/Your\sdevice\sis\sready\sfor\suse/', $messageBody):
                $response['status'] = 'clean';
                preg_match('/\<strong\>(.*)\<\/strong\>\<\/span\>\<\/p\>/', $messageBody, $matches);
                if(array_key_exists(1, $matches)) {
                    $response['status_details'] = $matches[1];
                }
                break;
            //Lost
            case preg_match('/warning/i', $messageBody) && preg_match('/this\sdevice\shas\sbeen\sreported\slost/', $messageBody):
                $response['status'] = 'lost';
                preg_match('/\<strong\>(.*)\<\/strong\>\<\/h5\>/', $messageBody, $matches);
                if(array_key_exists(1, $matches)) {
                    $response['status_details'] = $matches[1];
                }
                break;
            //Stolen
            case preg_match('/warning/i', $messageBody) && preg_match('/this\sdevice\shas\sbeen\sreported\sstolen/', $messageBody):
                $response['status'] = 'stolen';
                preg_match('/\<strong\>(.*)\<\/strong\>\<\/h5\>/', $messageBody, $matches);
                if(array_key_exists(1, $matches)) {
                    $response['status_details'] = $matches[1];
                }
                break;
            //Financed
            case preg_match('/device\sis\sbeing\sfinanced/', $messageBody):
                $response['status'] = 'financed';
                preg_match('/\<h5\sclass\=\"alert\"\>\<strong\>(.*)\<\/strong\>\<\/h5\>/s', $messageBody, $matches);
                if(array_key_exists(1, $matches)) {
                    $response['status_details'] = $matches[1];
                }
                break;
            //Incompatible  
            case preg_match('/We\sdo\snot\srecognize\sthe\sIMEI\snumber\syou\sentered/', $messageBody):
                $response['status'] = 'incompatible';
                preg_match('/\<h5\sclass\=\"alert\"\>\<strong\>(.*)\<\/strong\>\<\/h5\>/s', $messageBody, $matches);
                if(array_key_exists(1, $matches)) {
                    $response['status_details'] = $matches[1];
                }
                break;

            default:
                break;
        }

        return  $response;
    }

}

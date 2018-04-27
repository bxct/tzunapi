<?php

namespace Scrapers;

class Sprint extends \BaseScraper {

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

        try {
            $sprintResponse = $this->client->post('https://www.expomobile.com/tool.api/ajax/expoaws/index__aspx/checkEsn', [
                'headers' => [
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6,uk;q=0.4',
                    'Connection' => 'keep-alive',
//                        'Content-Length' => 229,
                    'User-Agent' => $userAgent,
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Host' => 'www.expomobile.com',
                    'Origin' => 'https://www.expomobile.com',
                    'Referer' => 'https://www.expomobile.com/expoaws/index.aspx/byod_device'
                ],
                'body' => [
                    'ProcessorID' => '30',
                    'Device' => 'Sprint',
                    'esn' => $esn,
                    'SimFree' => '0',
                    'cart' => '{"cart":[],"Byod":{"ProcessorID":"30","Device":"Sprint","esn":"' . $esn . '","SimFree":"0"}}',
                ],
                'allow_redirects' => false,
                'timeout' => 20
            ]);

            $sprintResponse = $sprintResponse->getBody();
            $statusResponseString = (string) $sprintResponse;
            return $this->interpretResponseMessage($statusResponseString);
        } catch (\Exception $e) {
            \Logger::write($e->getMessage(), 'custom', 'errors_' . array_slice(array_reverse(explode('\\', __CLASS__)),0,1)[0]);
            echo $e->getRequest() . "\n";
            if ($e->hasResponse()) {
                echo $e->getResponse() . "\n";
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
        
        $response = array(
            'status' => 'failed',
            'status_details' => 'could not parse response'
        );
        
        if($sprintResponse = json_decode($messageBody)) {
            switch(true) {
                case preg_match('/can be activated/i', $sprintResponse->mess) && property_exists($sprintResponse, 'ok') && ($sprintResponse->ok):
                    $response['status'] = 'clean';
                    $response['status_details'] = json_encode(get_object_vars($sprintResponse->sp));
                    break;
                case preg_match('/Device is in use/i', $sprintResponse->mess):
                    $response['status'] = 'balance';
                    $response['status_details'] = json_encode(get_object_vars($sprintResponse->sp));
                    break;
                case preg_match('/FRAUDULENT/i', $sprintResponse->mess):
                    $response['status'] = 'financed';
                    $response['status_details'] = json_encode(get_object_vars($sprintResponse->sp));
                    break;
                case preg_match('/STOLEN or LOST/i', $sprintResponse->mess):
                    $response['status'] = 'lost_stolen';
                    $response['status_details'] = json_encode(get_object_vars($sprintResponse->sp));
                    break;
                case $sprintResponse->mess == 'ESN can\'t be activated. ':
                default:
                    $response['status'] = 'incompatible';
                    $response['status_details'] = json_encode(get_object_vars($sprintResponse->sp));
                    break;
            }
        } else {
            \Logger::write('Response error', 'custom', 'errors_response_' . array_slice(array_reverse(explode('\\', __CLASS__)),0,1)[0]);
            \Logger::write($messageBody, 'custom', 'errors_response_' . array_slice(array_reverse(explode('\\', __CLASS__)),0,1)[0]);
        }

        return $response;
    }

}

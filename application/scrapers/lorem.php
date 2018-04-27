<?php

namespace Scrapers;

use GuzzleHttp\Client;

class Lorem extends \BaseScraper {

    protected $_isGSMA = false;
    
    public function process($esn, $implifiedDebugMode = false) {
        
        $response = array(
            'status' => 'failed',
            'status_details' => ''
        );
        
//        sleep(rand(0,20));
        
        switch (rand(1,5)) {
            case 1:
                $response['status'] = 'clean';
                $response['status_details'] = 'additional dummy status information';
                break;
            case 2:
                $response['status'] = 'balance';
                $response['status_details'] = 'additional dummy status information';
                break;
            case 3:
                $response['status'] = 'financed';
                $response['status_details'] = 'additional dummy status information';
                break;
            case 4:
                $response['status'] = 'lost_stolen';
                $response['status_details'] = 'additional dummy status information';
                break;
            case 5:
            default:
                $response['status'] = 'incompatible';
                $response['status_details'] = 'additional dummy status information';
                break;
        }

        return $response;
    }

}

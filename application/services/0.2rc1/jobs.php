<?php

/**
 * @ignore
 */

namespace Services_0_2rc1;

/**
 * Service for interaction with vendor instance through creating and controlling status of vendor jobs
 * 
 * @ignore
 */
class Jobs extends \Services_0_2rc1\BaseService {

    /**
     * Creates new Job according to the data received from frontend application
     * 
     * @param string $esn
     * @param integer $sub_query_id
     * @param integer $carrier_id
     * @param integer $user_id
     * @param boolean $sealed
     * @return \stdObject
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     */
    public static function add($esn = false, $sub_query_id = false, $carrier_id = false, $user_id = false, $sealed = false) {
        
        if(!$esn) {
            throw \ExceptionHandler::missingParameterException(__('You must specify ESN to add new job'));
        }
        if(!$sub_query_id) {
            throw \ExceptionHandler::missingParameterException(__('You must specify SubQuery ID to add new job'));
        }
        //Check if request was properly signed and is authorized
        if(!$user_id || !$sealed) {
            throw \ExceptionHandler::noAccessException(__('You must be authorized to add new job'));
        }
        
        //Create new Job
        $job  = new \Entities\Job();
        $job->setSubQueryId((int)$sub_query_id);
        $job->setCarrierId((int)$carrier_id);
        $job->setEsn($esn);
        \DS::getEntityManager()->persist($job);
        \DS::getEntityManager()->flush();
        
        $response = new \stdClass();
        $response->message = __('New Job was created');
        $response->job = $job;
        
        return $response;
    }
    
    /**
     * Retrieves status details of requested Job
     * 
     * @param integer $sub_query_id
     * @param boolean $user_id
     * @param boolean $sealed
     * 
     * @return array
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     * @throws \RequestFailed
     */
    public function poll($sub_query_id = false, $user_id = false, $sealed = false) {
        if(!$sub_query_id) {
            throw \ExceptionHandler::missingParameterException(__('You must specify SubQuery ID to add new job'));
        }
        //Check if request was properly signed and is authorized
        if(!$user_id || !$sealed) {
            throw \ExceptionHandler::noAccessException(__('You must be authorized to add new job'));
        }
        
        $response = new \stdClass();
        //Fetch Job
        if($job = \DS::getEM()->getRepository('\Entities\Job')->findOneBy(array( 'subQueryId' => $sub_query_id))) {
            //Send response with status details
            return $job->statusReport();
        } else {
            throw \ExceptionHandler::requestFailed('Job was not found');
        }
    }
    
    
    /**
     * Runs the scraping process and calls frontend back with status update
     * 
     * @param boolean $sealed
     * @param string $request_method waits for command line call ($request_method=CLI)
     * 
     * @throws \WrongParameterException
     * @throws \NoAccessException
     */
    public static function process($sealed = false, $request_method = false) {
        
        if ($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__(' Operation is not accessible this way.'));
        }
        if(!$sealed) {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        //Max execution time must not be a bottleneck of the script
        set_time_limit(false);
        
        for($i=0; $i<\Config::read('jobs_limit_per_process_iteration', 10); $i++) {
            //Fetch a few Jobs per iteration: 5 Jobs with different carriers
            if($jobs = \DS::getEM()->getRepository('\Entities\Job')->findBy(array('started' => null, 'canceled' => null, 'completed' => null, 'failed' => null), null, 1, 0)) {

                //Collect carrier titles
                $carriers = array();
                foreach(\DS::getEM()->getRepository('\Entities\Carrier')->findAll() as $carrier) {
                    $carriers[$carrier->getId()] = str_replace(array('&', '-'), array('n', '_'), $carrier->getTitle());
                }

                foreach($jobs as &$job) {
                    
                    $job->setStarted(new \DateTime());
                    \DS::getEM()->persist($job);
                    \DS::getEM()->flush();

                    //Run Jobs one-by-one using corresponding Scrapers - each call with randomized pretending of browser and cookie data
                    $scraperName = '\Scrapers\\' . $carriers[$job->getCarrierId()];
                    $scraper = new $scraperName();

                    //Start the Job so it is not captured by future process
                    if($status = $scraper->process($job->getEsn())) {
                        //Update statuses
                        switch($status['status']) {
                            case 'failed':
                                $job->setFailed(new \DateTime());
                                break;
                            default:
                                $job->setCompleted(new \DateTime());
                                break;
                        }
                    } else {
                        $status = array(
                            'status' => 'failed',
                            'status_details' => 'Unable to check data'
                        );
                        $job->setFailed(new \DateTime());
                    }

                    //Update status info
                    $job->setStatus($status['status']);
                    $job->setStatusDetails($status['status_details']);
                    \DS::getEM()->persist($job);
                    \DS::getEM()->flush();
                    \DS::getEM()->clear();

                    //Call frontend back with status updates
                    $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . \Config::read('api_endpoint'));
                    $data = array(
                        'service' => 'query',
                        'operation' => 'status_callback',
                        'version' => '0.1',
                        'sub_query_id' => $job->getSubQueryId(),
                        'status' => $status['status'],
                        'status_details' => $status['status_details'],
                        'format' => 'json'
                    );
                    $vendorResponse = json_decode($apiClient->send($data, 'POST'));
                }
            } else {
                throw \ExceptionHandler::wrongParametersException(__('No jobs currently pending'));
            }
        }
    }
    
    /**
     * Cancels Job which was not yet started
     * 
     * @param integer $sub_query_id
     * @param integer $user_id
     * @param integer $sealed
     * 
     * @return \stdClass
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     */
    public static function cancel($sub_query_id = false, $user_id = false, $sealed = false) {
        
        if(!$sub_query_id) {
            throw \ExceptionHandler::missingParameterException(__('You must specify SubQuery ID to add new job'));
        }
        //Check if request was properly signed and is authorized
        if(!$user_id || !$sealed) {
            throw \ExceptionHandler::noAccessException(__('You must be authorized to add new job'));
        }
        
        $response = new \stdClass();
        if($job = \DS::getEM()->getRepository('\Entities\Job')->findOneBy(array( 'subQueryId' => $sub_query_id, 'started' => null, 'canceled' => null, 'completed' => null, 'failed' => null))) {
            $job->setCanceled(new \DateTime);
            $job->setStatus('canceled');
            $job->setStatusDetails('Canceled by client');
            \DS::getEM()->persist($job);
            \DS::getEM()->flush();
            $response->message = __('Job was canceled');
            $response->job = $job;
        } else {
            throw \ExceptionHandler::requestFailed('Job is not available');
        }
        
        return $response;
    }

}

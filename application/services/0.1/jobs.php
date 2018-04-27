<?php

/**
 * @ignore
 */

namespace Services_0_1;

/**
 * Service for interaction with vendor instance through creating and controlling status of vendor jobs
 * 
 * @ignore
 */
class Jobs extends \Services_0_1\BaseService {

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
    public function add($esn = false, $sub_query_id = false, $carrier_id = false, $user_id = false, $sealed = false) {
        
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
        set_time_limit(1800);
        
        $publisherClass = '\JobPublishers\\' . \Config::read('job_publisher');
        if(!class_exists($publisherClass)) {
            throw \ExceptionHandler::systemFailureException(__('Publisher does not exist'));
        }
        
        $publisher = new $publisherClass();
        
        $publisher->processJobs();
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

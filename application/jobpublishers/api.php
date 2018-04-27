<?php

namespace JobPublishers;

class Api implements \Interfaces\JobPublisher{
    
    private $apiClients = array();
    
    /**
     * Creates an ApiClient instance or returns the one which already exists
     * 
     * @return \ApiClient
     */
    public function getClient($vendorId) {
        if(!array_key_exists($vendorId, $this->apiClients)) {
            if($vendor = \DS::getEM()->getRepository('\Entities\Vendor')->find($vendorId)) {
                $this->apiClients[$vendorId] = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . $vendor->getHostname());
            } else {
                return null;
            }
        }
        return $this->apiClients[$vendorId];
    }
    
    /** 
     * Publishes specific jobs to a vendor queue
     * 
     * @param string $esn
     * @param integer $vendorId
     * @param integer $carrierId
     * @param integer $subQueryId
     */
    public function publishJob($esn, $vendorId, $carrierId, $subQueryId) {
        
        if($apiClient = $this->getClient($vendorId)) {
            $data = array(
                'service' => 'jobs',
                'operation' => 'add',
                'version' => '0.1',
                'esn' => $esn,
                'sub_query_id' => $subQueryId,
                'carrier_id' => $carrierId,
                'format' => 'json'
            );

            if($response = json_decode($apiClient->send($data, 'POST'))) {
                if ($response->status == 'success') {
                    //2. In case of success => return true
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * Retrieves the Jobs for processing from the database
     * 
     * @param integer $portionSize the limit of Jobs query
     * @return array
     */
    public static function fetchJobs($portionSize) {
        return \DS::getEM()->getRepository('\Entities\Job')->findBy(array('started' => null, 'canceled' => null, 'completed' => null, 'failed' => null), null, $portionSize, 0);
    }
    
    /**
     * Fetches and processes portions of pending jobs
     * 
     * @throws \WrongParametersException
     */
    public function processJobs() {
        //Fetch a few Jobs per iteration: all Jobs with different carriers
        if($jobs = $this->fetchJobs(\Config::read('jobs_limit_per_process_iteration', 3))) {
            //Collect carrier titles
            $carriers = array();
            foreach(\DS::getEM()->getRepository('\Entities\Carrier')->findAll() as $carrier) {
                $carriers[$carrier->getId()] = str_replace(array('&', '-'), array('n', '_'), $carrier->getTitle());
            }
            
            foreach($jobs as &$job) {
                //Start the Job so it is not captured by future process
                $job->setStarted(new \DateTime());
                \DS::getEM()->persist($job);
            }
            \DS::getEM()->flush();
            
            $scrapers = array();
            
            foreach($jobs as &$job) {
                
                //Run Jobs one-by-one using corresponding Scrapers - each call with randomized pretending of browser and cookie data
                if(!array_key_exists($carriers[$job->getCarrierId()], $scrapers)) {
                    $scraperName = '\Scrapers\\' . $carriers[$job->getCarrierId()];
                    $scrapers[$carriers[$job->getCarrierId()]] = new $scraperName(new \GuzzleHttp\Client());
                }
                
                $job->process($scrapers[$carriers[$job->getCarrierId()]]);
            }
            \DS::getEM()->flush();
            \DS::getEM()->clear();
        } else {
            throw \ExceptionHandler::wrongParametersException(__('No jobs currently pending'));
        }
    }
}

<?php

namespace JobPublishers;

class Amqp implements \Interfaces\JobPublisher{
    
    private $rabbitMQ = null;
    
    private $takenJobs = 0;
    
    public function __construct() {
        $this->rabbitMQ = new \RabbitMQ();
    }
    
    /**
     * Counter for jobs accomplished during the current process run
     * 
     * @var integer
     */
    private $processedJobsCounter = 0;
    
    /** 
     * Publishes specific jobs to a vendor queue
     * 
     * @param string $esn
     * @param integer $vendorId
     * @param integer $carrierId
     * @param integer $subQueryId
     */
    public function publishJob($esn, $vendorId, $carrierId, $subQueryId) {
        return $this->rabbitMQ->publishMessage('jobs_v' . $vendorId, \Config::read('amqp_exchange') . $vendorId, json_encode(array(
            'esn' => $esn,
            'carrier_id' => $carrierId,
            'sub_query_id' => $subQueryId
        )));
    }
    
    /**
     * Subscribes to an AMQP query and processes messages
     */
    public function processJobs() {
        $segmentId = ftok(__FILE__, 'x');
        if(\ProcessLocker::lock($segmentId, \Config::read('parallel_amqp_listeners_limit', 10))) {
            //Listen to RabbitMQ stack
            $this->rabbitMQ->listen(array($this, 'processAJob'));
            
            //Release lock when finished
            if(!\ProcessLocker::unlock()) {
                throw \ExceptionHandler::systemFailureException('Could not unlock the resources.');
            }
            
            return $this->processedJobsCounter;
            
        } else {
            echo "\r\nUnable to acquire lock.\r\n";
        }
    }
    
    /**
     * Runs processing of a job
     * 
     * @param \PhpAmqpLib\Message\AMQPMessage $message a message from AMQP stack
     * 
     * @return boolean
     */
    public function processAJob(\PhpAmqpLib\Message\AMQPMessage $message) {
        
        $messageBody = $message->getBody();
        
        $response = false;
        
        if($jobSettings = json_decode($messageBody, true)) {
            
            $job = new \Entities\Job();
            
            $job->setEsn($jobSettings['esn']);
            $job->setCarrierId($jobSettings['carrier_id']);
            $job->setSubQueryId($jobSettings['sub_query_id']);
            $job->setStarted(new \DateTime());

            //Collect carrier titles
            $carriers = array();
            foreach (\DS::getEM()->getRepository('\Entities\Carrier')->findAll() as $carrier) {
                $carriers[$carrier->getId()] = str_replace(array('&', '-'), array('n', '_'), $carrier->getTitle());
            }
            
            $scraperName = '\Scrapers\\' . $carriers[$job->getCarrierId()];
            $job->process(new $scraperName(new \GuzzleHttp\Client()));
            
            \DS::getEM()->flush();
            \DS::getEM()->clear();
            
            $this->processedJobsCounter++;
            
            $response = true;
        } else {
            \Logger::write($messageBody, 'custom', 'rabbit_message_errors');
        }
        
        if($this->processedJobsCounter>=10) {
            exit;
        }
        
        return $response;
    }
    
    public function __destruct() {
        $this->rabbitMQ->disconnect();
    }
    
}

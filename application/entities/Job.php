<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Job
 */
class Job extends \BaseEntity
{
    /**
     * @var integer
     */
    private $subQueryId;

    /**
     * @var integer
     */
    private $carrierId;

    /**
     * @var string
     */
    private $esn;

    /**
     * @var \DateTime
     */
    private $started;

    /**
     * @var \DateTime
     */
    private $canceled;

    /**
     * @var \DateTime
     */
    private $completed;

    /**
     * @var \DateTime
     */
    private $failed;
    
    /**
     * @var string
     */
    private $status;
    
    /**
     * @var string
     */
    private $statusDetails;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var char
     */
    private $pid;


    /**
     * Set subQueryId
     *
     * @param integer $subQueryId
     * @return SubQuery
     */
    public function setSubQueryId($subQueryId)
    {
        $this->subQueryId = $subQueryId;
    
        return $this;
    }

    /**
     * Get queryId
     *
     * @return integer 
     */
    public function getSubQueryId()
    {
        return $this->subQueryId;
    }

    /**
     * Set carrierId
     *
     * @param integer $carrierId
     * @return SubQuery
     */
    public function setCarrierId($carrierId)
    {
        $this->carrierId = $carrierId;
    
        return $this;
    }

    /**
     * Get carrierId
     *
     * @return integer 
     */
    public function getCarrierId()
    {
        return $this->carrierId;
    }

    /**
     * Set vendorId
     *
     * @param integer $vendorId
     * @return SubQuery
     */
    public function setVendorId($vendorId)
    {
        $this->vendorId = $vendorId;
    
        return $this;
    }

    /**
     * Get vendorId
     *
     * @return integer 
     */
    public function getVendorId()
    {
        return $this->vendorId;
    }

    /**
     * Set esn
     *
     * @param string $esn
     * @return SubQuery
     */
    public function setEsn($esn)
    {
        $this->esn = $esn;
    
        return $this;
    }

    /**
     * Get esn
     *
     * @return string 
     */
    public function getEsn()
    {
        return $this->esn;
    }
    
    /**
     * Set the PID of process working on job
     *
     * @param \DateTime $pid
     * @return \Entities\Job
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    
        return $this;
    }

    /**
     * Set started
     *
     * @param \DateTime $started
     * @return SubQuery
     */
    public function setStarted($started)
    {
        $this->started = $started;
    
        return $this;
    }

    /**
     * Get started
     *
     * @return \DateTime 
     */
    public function getStarted()
    {
        return $this->started;
    }
    
    /**
     * Set canceled
     *
     * @param \DateTime $canceled
     * @return Query
     */
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;
    
        return $this;
    }

    /**
     * Get canceled
     *
     * @return \DateTime 
     */
    public function getCanceled()
    {
        return $this->canceled;
    }

    /**
     * Set completed
     *
     * @param \DateTime $completed
     * @return SubQuery
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    
        return $this;
    }

    /**
     * Get completed
     *
     * @return \DateTime 
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set failed
     *
     * @param \DateTime $failed
     * @return SubQuery
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;
    
        return $this;
    }

    /**
     * Get failed
     *
     * @return \DateTime 
     */
    public function getFailed()
    {
        return $this->failed;
    }
    
    /**
     * Set status
     *
     * @param string $status
     * @return Query
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Defines some rules to return status abbreviation of the query
     * 
     * @return string
     */
    public function getFullStatus() {
        $status = $this->getStatus(); 
        switch(true) {
            case empty($status):
                $status = 'in_progress';
                break;
            default:
                break;
        }
        return $status;
    }
    
    /**
     * Set status details
     *
     * @param string $statusDetails
     * @return Query
     */
    public function setStatusDetails($statusDetails)
    {
        $this->statusDetails = is_array($statusDetails)?json_encode($statusDetails):$statusDetails;
    
        return $this;
    }

    /**
     * Get status details
     *
     * @return string 
     */
    public function getStatusDetails()
    {
        $statusDetailsArray = json_decode($this->statusDetails, TRUE);
        if(is_array($statusDetailsArray)) {
            if(array_key_exists('regular_status_details', $statusDetailsArray)) {
                return $statusDetailsArray['regular_status_details'];
            }
            return $statusDetailsArray;
        }
        return $this->statusDetails;
    }
    
    /**
     * Returns bare response details (mostly for Verizon testing)
     * 
     * @return boolean|string
     */
    public function getMoreStatusDetails() {
        $statusDetailsArray = json_decode($this->statusDetails, TRUE);
        if(is_array($statusDetailsArray) && array_key_exists('more_status_details', $statusDetailsArray)) {
            return $statusDetailsArray['more_status_details'];
        }
        return false;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return SubQuery
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return SubQuery
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    
        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get pid
     *
     * @return integer 
     */
    public function getPid()
    {
        return $this->pid;
    }
    
    /**
     * Sends request to the associated vendor and starts the Job
     */
    public function publish() {
        /**
         * @todo make an API call to an associated Vendor (if vendor actually is associated)
         * 1. Try to publish Job to a Vendor
         * 2. In case of success => set `started` date
         */
        return true;
    }
    
    /**
     * Prevents associated Job from proceeding
     */
    public function stop() {
        /**
         * @todo make a call to an appropriate Vendor to cancel job
         */
        return true;
    }
    
    /**
     * Provides details on a SubQuery status: status abbreviation and status details
     * @return type
     */
    public function statusReport() {
        return array(
            'status' => $this->getStatus(),
            'status_details' => $this->getStatusDetails(),
            'started' => $this->getStarted()?$this->getStarted()->format('Y-m-d H:i:s'):null,
            'canceled' => $this->getCanceled()?$this->getCanceled()->format('Y-m-d H:i:s'):null,
            'completed' => $this->getCompleted()?$this->getCompleted()->format('Y-m-d H:i:s'):null,
            'failed' => $this->getFailed()?$this->getFailed()->format('Y-m-d H:i:s'):null,
        );
    }
    
    /**
     * Runs an individual Job processing
     * 
     * @param \BaseScraper $scraper
     */
    public function process($scraper) {
        if($status = $scraper->process($this->getEsn())) {
            //Update statuses
            switch ($status['status']) {
                case 'failed':
                    $this->setFailed(new \DateTime());
                    break;
                default:
                    $this->setCompleted(new \DateTime());
                    break;
            }
        } else {
            $status = array(
                'status' => 'failed',
                'status_details' => 'Unable to check data'
            );
            $this->setFailed(new \DateTime());
        }

        //Update status info
        $this->setStatus($status['status']);
        $this->setStatusDetails($status['status_details']);
        $this->setPid(getmypid());
        
        \DS::getEM()->persist($this);
\Logger::write('Trying to call API', 'custom', 'job_callbacks');
        //Call frontend back with status updates
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . \Config::read('api_endpoint'));
        $data = array(
            'service' => 'query',
            'operation' => 'status_callback',
            'version' => '0.1',
            'sub_query_id' => $this->getSubQueryId(),
            'status' => $this->getStatus(),
            'status_details' => $this->getStatusDetails(),
            'format' => 'json'
        );
        $rawData = $apiClient->send($data, 'POST');
\Logger::write($rawData, 'custom', 'job_callbacks');
        
        $vendorResponse = json_decode($apiClient->send($data, 'POST'));
\Logger::write($vendorResponse, 'custom', 'job_callbacks');
        if($vendorResponse && $vendorResponse->status=='success' && $vendorResponse->body === true) {
            return true;
        }
        return false;
    }
}

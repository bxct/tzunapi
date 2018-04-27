<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubQuery
 */
class SubQuery extends \BaseEntity
{
    
    /**
     * @var \Interfaces\JobPublisher
     */
    private $publisher;
    
    /**
     * @var integer
     */
    private $queryId;

    /**
     * @var integer
     */
    private $carrierId;

    /**
     * @var integer
     */
    private $vendorId;

    /**
     * @var string
     */
    private $esn;
    
    /**
     * @var integer
     */
    private $attempts;

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
     * Overrides the default constructor of parent, but includes a related call
     * 
     * @param \Interfaces\JobPublisher $publisher
     */
    public function __construct(\Interfaces\JobPublisher $publisher) {
        parent::__construct();
        $this->setPublisher($publisher);
    }
    
    /**
     * Specifies the job publisher for the entity
     * 
     * @param \Interfaces\JobPublisher $publisher
     */
    public function setPublisher(\Interfaces\JobPublisher $publisher) {
        if(empty($this->publisher))
            $this->publisher = $publisher;
    }

    /**
     * Set queryId
     *
     * @param integer $queryId
     * @return SubQuery
     */
    public function setQueryId($queryId)
    {
        $this->queryId = $queryId;
    
        return $this;
    }

    /**
     * Get queryId
     *
     * @return integer 
     */
    public function getQueryId()
    {
        return $this->queryId;
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
     * Set attempts
     *
     * @param integer $queryId
     * @return SubQuery
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
    
        return $this;
    }

    /**
     * Get attempts
     *
     * @return integer 
     */
    public function getAttempts()
    {
        return $this->attempts;
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
            $this->statusDetails = $statusDetailsArray;
        }
        return $this->statusDetails;
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
     * Sends request to the associated vendor and starts the Job
     * 
     * @param \Entities\Vendor $vendor
     * @param integer $attempts
     * @param integer $waitTimeout
     * @return boolean
     */
    public function publish($vendor = false, $attempts = 1, $waitTimeout = 0) {
        //Try to publish Job to a Vendor if publisher is available
        if($this->publisher) {
            for ($i = 0; $i < $attempts; $i++) {
                if ($this->publisher->publishJob($this->getEsn(), $this->getVendorId(), $this->getCarrierId(), $this->getId())) {
                    $this->setStarted(new \DateTime());
                    \DS::getEM()->persist($this);
                    return true;
                }
                sleep($waitTimeout);
            }
        }

        return false;
        
    }
    
    /**
     * Updates the status fields of \SubQuery object
     * 
     * @param string $status
     * @param string $statusDetails
     * @return \Entities\SubQuery
     */
    public function updateStatus($status, $statusDetails) {
        \Logger::write($status, 'custom', 'callback');
        \Logger::write($statusDetails, 'custom', 'callback');
        //Update statuses
        switch($status) {
            case 'failed':
                $q = \DS::getEM()->createQuery('UPDATE \Entities\Vendor v SET v.failed=(v.failed+1) WHERE v.id = :vid');
                $q->setParameter('vid', $this->getVendorId());
                $q->execute();
                
                //If failed => try to reassign SubQuery to another Vendor:
                // ... 1. Try to find free Vendor ...
                $qb = \DS::getEM()->createQueryBuilder();
                $qb
                    ->select('v', '(v.stackSize-v.completed-v.failed) AS active_jobs')
                    ->from('Entities\Vendor', 'v')
                    ->leftJoin(
                        'Entities\CarriersVendor',
                        'cv',
                        \Doctrine\ORM\Query\Expr\Join::WITH,
                        'v.id = cv.vendorId'
                    )
                    ->where('cv.carrierId = :carrier_id')
                    ->andWhere('cv.vendorId <> :vendor_id')
                    ->andWhere('v.activated IS NOT NULL')
                    ->andWhere('v.disabled IS NULL')
                    ->setParameter('carrier_id', $this->getCarrierId())
                    ->setParameter('vendor_id', $this->getVendorId())
                    ->addOrderBy('active_jobs', 'ASC')
                    ->addOrderBy('v.failed', 'ASC')
                    ->setMaxResults(1);
                // ... 2.Reassign SubQuery to another Vendor if possible.
                if(($mostFreeVendor = $qb->getQuery()->getResult()) && (array_key_exists(0, $mostFreeVendor)) && ($this->getAttempts()<\Config::read('max_carrier_attempts'))) {
                    $this->setVendorId($mostFreeVendor[0][0]->getId());
                    $this->publish($mostFreeVendor[0][0]);
                    $this->setStarted(new \DateTime());
                    //Increment Vendor stack
                    $q = \DS::getEM()->createQuery('UPDATE \Entities\Vendor v SET v.stackSize=(v.stackSize+1) WHERE v.id = :vid');
                    $q->setParameter('vid', $mostFreeVendor[0][0]->getId());
                    $q->execute();
                    //Increment SubQuery attempts
                    $q = \DS::getEM()->createQuery('UPDATE \Entities\SubQuery sq SET sq.attempts=(sq.attempts+1) WHERE sq.id = :squid');
                    $q->setParameter('squid', $this->getId());
                    $q->execute();
                } else {
                    // ... all hope is lost
                    $this->setStatus($status);
                    $this->setStatusDetails($statusDetails);
                    $this->setFailed(new \DateTime());
                    $this->setCompleted(new \DateTime());
                }
                break;
            default:
                \Logger::write('not failed: increment completed here', 'custom', 'callback');
                $q = \DS::getEM()->createQuery('UPDATE \Entities\Vendor v SET v.completed=(v.completed+1) WHERE v.id = :vid');
                $q->setParameter('vid', $this->getVendorId());
                $q->execute();
                $this->setStatus($status);
                $this->setStatusDetails($statusDetails);
                $this->setCompleted(new \DateTime());
                break;
        }
        \Logger::write('done', 'custom', 'callback');
        return $this;
    }
    
    /**
     * Calls the Vendor to find out what current status of respective Job
     */
    public function pollJobStatus() {
        $vendor = \DS::getEM()->getRepository('\Entities\Vendor')->find($this->getVendorId());
        $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . $vendor->getHostname());
        \Logger::write('Trying to poll status of old job', 'custom', 'job_callbacks');
        $data = array(
            'service' => 'jobs',
            'operation' => 'poll',
            'version' => '0.1',
            'sub_query_id' => $this->getId(),
            'format' => 'json'
        );
        
        $rawResponse = $apiClient->send($data, 'POST');
        
        if($response = json_decode($rawResponse)) {
            \Logger::write($response, 'custom', 'job_callbacks');
            if($response->status == 'success') {
                return $response->body;
            }
        } else {
        \Logger::write($rawResponse, 'custom', 'job_callback_errors');
        }
        
        return false;
    }
    
    /**
     * Prevents associated Job from proceeding
     */
    public function stop() {
        
        if($vendorId = $this->getVendorId()) {
            if($vendor = \DS::getEM()->getRepository('\Entities\Vendor')->find($vendorId)) {
                $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . $vendor->getHostname());

                $data = array(
                    'service' => 'jobs',
                    'operation' => 'cancel',
                    'version' => '0.1',
                    'sub_query_id' => $this->getId(),
                    'format' => 'json'
                );

                if($response = json_decode($apiClient->send($data, 'POST'))) {
                    if($response->status == 'success') {
                        return true;
                    }
                }
            }
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Provides details on a SubQuery status: status abbreviation and status details
     * @return type
     */
    public function statusReport() {
        return array(
            'status' => $this->getFullStatus(),
            'status_details' => $this->getStatusDetails()
        );
    }
}

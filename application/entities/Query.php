<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Query
 */
class Query extends \BaseEntity
{
    /**
     * @var integer
     */
    private $deviceId;

    /**
     * @var integer
     */
    private $userId;

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
     * @var \DateTime
     */
    private $gsmaStarted;
    
    /**
     * @var \DateTime
     */
    private $gsmaCompleted;
        
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
     * Set deviceId
     *
     * @param integer $deviceId
     * @return Query
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
    
        return $this;
    }

    /**
     * Get deviceId
     *
     * @return integer 
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return Query
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set esn
     *
     * @param string $esn
     * @return Query
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
     * Set started
     *
     * @param \DateTime $started
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * Set gsmaStarted
     *
     * @param \DateTime $gsmaStarted
     * @return Query
     */
    public function setGsmaStarted($gsmaStarted)
    {
        $this->gsmaStarted = $gsmaStarted;
    
        return $this;
    }

    /**
     * Get gsmaStarted
     *
     * @return \DateTime 
     */
    public function getGsmaStarted()
    {
        return $this->gsmaStarted;
    }
    
    /**
     * Set gsmaCompleted
     *
     * @param \DateTime $gsmaCompleted
     * @return Query
     */
    public function setGsmaCompleted($gsmaCompleted)
    {
        $this->gsmaCompleted = $gsmaCompleted;
    
        return $this;
    }

    /**
     * Get gsmaCompleted
     *
     * @return \DateTime 
     */
    public function getGsmaCompleted()
    {
        return $this->gsmaCompleted;
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
        //Regexp to check if string is valid JSON
        $pcre_regex = '
  /
  (?(DEFINE)
     (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )    
     (?<boolean>   true | false | null )
     (?<string>    " ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
     (?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
     (?<pair>      \s* (?&string) \s* : (?&json)  )
     (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
     (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
  )
  \A (?&json) \Z
  /six   
';
        if (preg_match($pcre_regex, $this->statusDetails)) {
            $statusDetailsArray = json_decode($this->statusDetails, TRUE);
            if (json_last_error() == JSON_ERROR_NONE) {
                if (is_array($statusDetailsArray)) {
                    $this->statusDetails = $statusDetailsArray;
                }
            }
        }
        return $this->statusDetails;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Query
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
     * @return Query
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
     * Specifies a series of params based on params from passed \Entities\Device object
     * 
     * @param \Entities\Device $device
     * @return Query
     */
    public function setDevice(\Entities\Device $device) {
        $this->setDeviceId($device->getId());
        $this->setEsn($device->getEsn());
        $this->setUserId($device->getUserId());
        return $this;
    }
    
    /**
     * Updates Query status according to status of all completed SubQueries
     * If at least one SubQuery is failed, overall status will be failed too.
     */
    public function updateStatus() {
        $this->setStatus('completed');
        //Collect all SubQueries        
        if($subQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findBy(array('queryId' =>$this->getId()))) {            
            foreach($subQueries as &$subQuery) {
                if($subQuery->getCompleted()) {
                    //Set common status failed if at least one SubQuery failed
                    if($subQuery->getStatus() == 'failed') {
                        $this->setFailed(new \DateTime());
                        $this->setStatus('failed');
                        break;
                    }
                } else {
                    return $this;
                }
            }
        }
        
        //Set completed datetime if completed
        $this->setCompleted(new \DateTime());
        /**
         * @todo Send callback if callback URL was specified
         */
        
        return $this;
    }
    
    /**
     * Provides details on Query status per each carrier
     * 
     * @return array
     */
    public function statusReport() {
        
        $deviceStatuses = array();
        
        $response = array(
            'status' => $this->getFullStatus(),
            'status_details' => $this->getStatusDetails(),
            'carriers' => array()
        );
        //For already existing queries
        if(isset($response['status_details']['gsma'])) {
            $response['status_details']['global'] = $response['status_details']['gsma'];
            unset($response['status_details']['gsma']);
        }
        
        if(isset($response['status_details']['global']) && !empty($response['status_details']['global']['status'])) {
            $deviceStatuses[] = $response['status_details']['global']['status'];
        }        
                
        $qb = \DS::getEM()->createQueryBuilder();
        $qb
            ->select(array('sq AS sub_query', 'c.title AS carrier_title'))
            ->from('Entities\SubQuery', 'sq')
            ->innerJoin(
                'Entities\Carrier',
                'c',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'sq.carrierId = c.id'
            )
            ->where('sq.queryId = :query_id')
            ->setParameter('query_id', $this->getId())
            ->orderBy('c.id', 'ASC');
        
        if($subQueries = $qb->getQuery()->getResult()) {
            foreach($subQueries as &$sq) {
                $response['carriers'][$sq['carrier_title']] = $sq['sub_query']->statusReport();
                $response['carriers'][$sq['carrier_title']]['carrier_title'] = $sq['carrier_title'];
                $deviceStatuses[] =  $response['carriers'][$sq['carrier_title']]['status'];
            }
        }
        
        $deviceStatus = 'clean';
        foreach($deviceStatuses as $dS) {
            switch($dS):
                case 'blacklisted':
                    $deviceStatus = $dS;
                    break;
                case 'lost_stolen':
                    $deviceStatus = $dS;
                    break;
                case 'lost':
                    $deviceStatus = $dS;
                    break;
                case 'stolen':
                    $deviceStatus = $dS;
                    break;
                case 'financed':
                    $deviceStatus = $dS;
                    break;
                 case 'balance':
                    $deviceStatus = $dS;
                    break;
            endswitch;
        }
        
        $response['device_status'] = $deviceStatus;      
        
        
        return (object)$response;
    }
}

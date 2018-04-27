<?php
/**
 * Holds internal system cron jobs
 * 
 * @author Anton Matiyenko <amatiyenko@gmail.com>
 * 
 * @ignore
 */

namespace Services_0_1;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Provides functions for cron CLI
 * 
 * @ignore
 */
class Cron extends \Services_0_1\BaseService {

    /**
     * Checks if there are any SubQueries which have not been assigned to any vendor
     * 
     * /var/www/cli.php cron/check_unassigned_sub_queries
     * 
     * @param string $request_method
     * 
     * @throws \NoAccessException
     */
    public static function check_unassigned_sub_queries($request_method) {
        if ($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        //Find stack of unassigned sub queries
        if($subQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findBy(array('vendorId' => null, 'started' => null, 'completed' => null, 'canceled' => null, 'failed' => null), null, 5, 0)) {
            //Find most available Vendor
            $vendorsByCarrier = \DS::getEntityManager()->getRepository('Entities\Vendor')->getVendorsByCarrier();
            
            foreach($subQueries as &$subQuery) {
                //Prevent SubQuery from being processed again later by this action or by check_old_sub_queries()
                $subQuery->setFailed(new \DateTime());
                \DS::getEM()->persist($subQuery);
                \DS::getEM()->flush();
            }
            
            foreach($subQueries as &$subQuery) {
                
                $newVendorCreated = false;
                $availableVendor = null;
                
                //Check if Vendor was available at the moment of call
                if (array_key_exists($subQuery->getCarrierId(), $vendorsByCarrier)) {
                    //Yes - use the vendor that has been found
                    $availableVendor = $vendorsByCarrier[$subQuery->getCarrierId()];
                } else {
                    //No - try to create a new Vendor
                    if(!$newVendorCreated && \DS::getEM()->getRepository('\Entities\Vendor')->create()) {
                        sleep(30);
                        //Collect vendors again
                        $vendorsByCarrier = \DS::getEntityManager()->getRepository('Entities\Vendor')->getVendorsByCarrier();
                        if (array_key_exists($subQuery->getCarrierId(), $vendorsByCarrier)) {
                            //Yes - use the vendor that has been found
                            $availableVendor = $vendorsByCarrier[$subQuery->getCarrierId()];
                        } else {
                            //Creating new Vendor didn't help :(
                            continue;
                        }
                    } else {
                        //Could not create new Vendor or created Vendor does not support the carrier
                        continue;
                    }
                }
                if($availableVendor) {
                    $subQuery->setFailed(null);
                    $subQuery->setVendorId($availableVendor->getId());
                    $subQuery->setAttempts(1);

                    //Increment Vendor stack
                    $q = \DS::getEM()->createQuery('UPDATE \Entities\Vendor v SET v.stackSize=(v.stackSize+1) WHERE v.id = :vid');
                    $q->setParameter('vid', $availableVendor->getId());
                    $q->execute();

                    //Publish SubQuery at Vendor instance
                    $subQuery->publish($availableVendor, 3, 10);
                    \DS::getEM()->flush();
                }
            }
            return true;
        }
        
        return false;
    }

    /**
     * 
     * 
     * @param string $request_method
     * @return boolean
     * 
     * @throws \NoAccessException
     */
    public static function check_old_sub_queries($request_method) {
        if ($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        $now = new \DateTime();
        //Find old unfished SubQueries (in portions)
        //Fetch respective SubQuery and update it's status
        $qb = \DS::getEM()->createQueryBuilder();
        $qb
            ->select('sq')
            ->from('Entities\SubQuery', 'sq')
            ->where('sq.started <= :started_date')
            ->andWhere('sq.started IS NOT NULL')
            ->andWhere('sq.vendorId IS NOT NULL')
            ->andWhere('sq.completed IS NULL')
            ->andWhere('sq.canceled IS NULL')
            ->andWhere('sq.failed IS NULL')
            ->setParameter('started_date', $now->add(\DateInterval::createFromDateString('-90 second')))
            ->setFirstResult( 0 )
            ->setMaxResults( 5 );
        if($subQueries = $qb->getQuery()->getResult()) {
            
            $publisherClass = '\JobPublishers\\' . \Config::read('job_publisher');
            $publisher = new $publisherClass();
            
            //Update SubQuery status accordingly if access granted
            //In fact there is only one SubQuery, loop is only to withdraw it from array returned by query            
            foreach($subQueries as &$subQuery) {
                
                $subQuery->setPublisher($publisher);
                
                //Poll respective vendor for each SubQuery's status
                if($jobStatus = $subQuery->pollJobStatus()) {
                    
                    \Logger::write($subQuery, 'custom', 'lost_sub_tasks');
                    \Logger::write($jobStatus, 'custom', 'lost_sub_tasks');
                    
                    $subQueryChanged = false;
                    //Update SubQuery status according to it's status in vendor database
                    
                    if($jobStatus->completed) {
                        $subQuery->setCompleted(new \DateTime($jobStatus->completed));
                        $subQueryChanged = true;
                    }
                    if($jobStatus->canceled) {
                        $subQuery->setCanceled(new \DateTime($jobStatus->canceled));
                        $subQueryChanged = true;
                    }
                    if($jobStatus->failed) {
                        $jobStatus->status = 'failed';
                        $subQuery->setFailed(new \DateTime($jobStatus->failed));
                        $subQueryChanged = true;
                    }
                    if($jobStatus->status) {
                        $subQueryChanged = true;
                    }
                    if($jobStatus->status_details) {
                        $subQueryChanged = true;
                    }
                    if($subQueryChanged) {
                        $subQuery->updateStatus($jobStatus->status, $jobStatus->status_details);
                        \DS::getEM()->persist($subQuery);
                        \DS::getEM()->flush();
                        //If all SubQueries finished => update common Query status
                        if(!($incompleteSubQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findBy(array('queryId' => $subQuery->getQueryId(), 'completed' => null)))) {
                            //Check Query status and update it too if this was the last SubQuery to wait => only in case of finishing of all of SubQueries
                            if($query = \DS::getEM()->getRepository('\Entities\Query')->find($subQuery->getQueryId())) {
                                $query->updateStatus();
                                \DS::getEM()->persist($query);
                                \DS::getEM()->flush();
                            }
                        }
                    }
                } else {
                    //If Vendor is unreachable
                    
                    /**
                     * @todo
                     * 
                     * 4. Check Vendor's status and availability
                     * 4.1. Check Vendor's recent SubQueries and do related actions
                     * 4.2. If too many failures for some Carrier => detach related Carrier
                     * 4.2.1. If no available Carriers for Vendor => deactivate Vendor
                     */
                    
                    $subQuery->updateStatus('failed', __('Vendor is unreachable'));
                    \DS::getEM()->persist($subQuery);
                    \DS::getEM()->flush();
                }
            }
            return true;
        } else {
            return false;
        }//Fetch respective SubQuery and update it's status
    }

    /**
     * 
     * 
     * @param type $request_method
     * @throws type
     */
    public static function query_canceled($request_method) {
        if ($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        /**
         * @todo
         * Implement deferred cancelation procedure for query which is pending
         * 1. Find all canceled queries
         * 2. Find every unfinished SubQuery and call it's Vendor to cancel => save some Vendor lifetime
         * 3. Update status for each SubQuery: completed=NOW()
         */
    }
    
    public static function process_gsma_queries($request_method) {
        if ($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        
        //Get all queries for which GSMA was not called
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria->where($criteria->expr()->isNull('gsmaStarted'));
        $criteria->andWhere($criteria->expr()->isNull('gsmaCompleted'));                
        $queriesToProcess = \DS::getEntityManager()->getRepository('Entities\Query')->matching($criteria);          
        if(!empty($queriesToProcess)) {         
            
            $scraper = new \BaseScraper(new \GuzzleHttp\Client()); 
             
            foreach($queriesToProcess as $query) {
             
                //Set started
               $query->setGsmaStarted(new \DateTime());
                \DS::getEM()->persist($query);
                \DS::getEM()->flush();
               
                $gsmaResponse = $scraper->process($query->getEsn());

                $statusDetails = array(
                    'global' => $gsmaResponse
                );
                
                $existingStatusDetails = $query->getStatusDetails();
                if(!empty($existingStatusDetails)) {
                    if(is_array($existingStatusDetails)) {                        
                        $statusDetails = array_merge($statusDetails, $existingStatusDetails);
                    } else {
                        $statusDetails['details'] =  $existingStatusDetails;
                    }
                }
                                
                $statusIsFinal  = true;                
                //check if there are un completed sub queries
                $subQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findBy(array(
                    'queryId' => $query->getId()));
               
                $queryFinalStatus = ($gsmaResponse['status'] == 'failed')?'failed':'completed'; 
                if(empty($subQueries)) {
                    //There are no subqueries
                    $statusIsFinal = true;                                     
                } else {                    
                    foreach($subQueries as $sQ) {  
                        $comlpleted = $sQ->getCompleted();
                        if(empty($comlpleted)) {
                            $statusIsFinal = false;
                            break;
                        } else {
                            if($sQ->getStatus() == 'failed') {
                                $queryFinalStatus = 'failed';
                            }
                        }
                    }
                }
                                    
                if($statusIsFinal) {
                    switch($queryFinalStatus) {
                        case 'failed':
                            $query->setStatus('failed');
                            $query->setFailed(new \DateTime());
                            break;
                        default:
                            $query->setStatus('completed');
                            $query->setCompleted(new \DateTime());
                            break;
                    }
                }        
               
                $query->setGsmaCompleted(new \DateTime());
                $query->setStatusDetails($statusDetails);
                \DS::getEM()->persist($query);
                \DS::getEM()->flush();
                
            }
        }
        
        return true;
    }
    
    /**
     * Balances the number of active instances according to the load trends (increasing or decreasing)
     * 
     * @param string $request_method
     * 
     * @throws \NoAccessException
     */
    public static function balance_load($request_method){
        if ($request_method !== 'CLI') {
            throw \ExceptionHandler::noAccessException(__('Maintenance console is not accessible this way.'));
        }
        /**
         * @todo
         * 1. Find out trend of load - increasing or decreasing
         * 2. Detect necessary number of instances
         * 3. Disable if necessary some instances
         * 4. Enable necessary number of instances
         * 4.1. Enable only instances disabled more than 10 minutes ago
         * 4.2. If there is no existing disabled instances matching the criteria, create new
         */
    }
    
    public static function consume_jobs() {
        
    }

}

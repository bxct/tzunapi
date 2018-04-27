<?php
/**
 * This file holds the Query service class
 *
 * @author Anton Matiyenko <amatiyenko@gmail.com>
 *
 */

namespace Services_0_1;

/**
 * This service maintains all primary ESN-related functionality, including functions to create (add()), cancel (cancel()) and check (poll()) status of the created ESN query
 */
class Query extends \Services_0_1\BaseService {

    /**
     * Adds a new Query with specified ESN to be checked in database of every supported carrier
     * 
     * @param string $esn the ESN number of device to be checked
     * @param integer $carrier_id the integer ID of the carrier to exclusively check ESN status on (other are skipped, default is "false") (1 - 'Verizon', 2 - 'AT&T', 3 - 'Sprint', 4 - 'T-Mobile', )
     * @param integer $user_id internal ID of the user with specified keypair - always overriden internally
     * @param boolean $force provides way to run fresh ESN verification query before the 24h period since last check passed
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     * 
     * @return \Entities\Query encoded array of all status data
     * <pre>
     * object(stdClass)#10 (2) {
     *     ["status"]=>
     *     string(7) "success"
     *     ["body"]=>
     *     object(stdClass)#11 (2) {
     *           ["message"]=>
     *           string(21) "Query has been added."
     *           ["query"]=>
     *           object(stdClass)#12 (14) {
     *               ["deviceId"]=>
     *               int(38)
     *               ["userId"]=>
     *               int(6)
     *               ["esn"]=>
     *               string(15) "352069XXXXXXX91"
     *               ["started"]=>
     *               object(stdClass)#13 (3) {
     *                     ["date"]=>
     *                     string(19) "2015-08-14 14:17:35"
     *                     ["timezone_type"]=>
     *                     int(3)
     *                     ["timezone"]=>
     *                     string(19) "America/Los_Angeles"
     *               }
     *               ["canceled"]=>
     *               NULL
     *               ["completed"]=>
     *               NULL
     *               ["failed"]=>
     *               NULL
     *               ["gsmaStarted"]=>
     *               NULL
     *               ["gsmaCompleted"]=>
     *               NULL
     *               ["status"]=>
     *               NULL
     *               ["statusDetails"]=>
     *               NULL
     *               ["created"]=>
     *               object(stdClass)#14 (3) {
     *                     ["date"]=>
     *                     string(19) "2015-08-14 14:17:35"
     *                     ["timezone_type"]=>
     *                     int(3)
     *                     ["timezone"]=>
     *                     string(19) "America/Los_Angeles"
     *               }
     *               ["modified"]=>
     *               object(stdClass)#15 (3) {
     *                     ["date"]=>
     *                     string(19) "2015-08-14 14:17:35"
     *                     ["timezone_type"]=>
     *                     int(3)
     *                     ["timezone"]=>
     *                     string(19) "America/Los_Angeles"
     *               }
     *               ["id"]=>
     *               int(267)
     *           }
     * }
     * </pre>
     * 
     * 
     * 
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     * @throws \SystemFailureException
     * @throws \NoAccessException
     */
    public static function add($esn = false, $carrier = false, $carrier_id = false, $user_id = false, $force = false, $sealed = false) {
        if(!$esn) {
            throw \ExceptionHandler::missingParameterException(__('You must specify ESN to create new query'));
        }
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to create new query'));
        }
        if($sealed) {
            //Create device by calling respective service operation (or find existing)
            if($device = \Services_0_1\Devices::create($esn, $user_id, $sealed)) {
                //Fetch list of active carriers
                if($supportedCarriers = \DS::getEntityManager()->getRepository('Entities\Carrier')->findActive()) {
                    //Find similar queries within last 24 hours ...
                    $now = new \DateTime();
                    $criteria = new \Doctrine\Common\Collections\Criteria();
                    $criteria->where($criteria->expr()->gt('created', $now->add(\DateInterval::createFromDateString('-1 day'))));
                    $criteria->andWhere($criteria->expr()->eq('userId', (int)$user_id));
                    $criteria->andWhere($criteria->expr()->eq('deviceId', $device->getId()));
                    $criteria->andWhere($criteria->expr()->eq('esn', $device->getEsn()));
                    $existingQueries = \DS::getEntityManager()->getRepository('Entities\Query')->matching($criteria);
                    // ... and if query does not exist or force parameter is specified ...
                    if($existingQueries->isEmpty() || $force) {
                        // ... create new query ... 
                        $query = new \Entities\Query();
                        $query->setDevice($device);
                        \DS::getEntityManager()->persist($query);
                        \DS::getEntityManager()->flush();
                        
                        \Logger::write('Retrieving vendors by carrier', 'custom', 'vendors_by_carrier');
                        
                        $vendorsByCarrier = \DS::getEntityManager()->getRepository('Entities\Vendor')->getVendorsByCarrier();
                        
                        $existingCarrier = false;
                        
                        if($carrier_id) {
                            $carrier = $carrier_id;
                        }
                        
                        if($carrier) {
                            if(isValueNumeric($carrier)) {
                                $existingCarrier = \DS::getEntityManager()->getRepository('\Entities\Carrier')->find(intval($carrier));
                            } else {
                                $existingCarrier = \DS::getEntityManager()->getRepository('\Entities\Carrier')->findOneByTitle($carrier);
                            }
                        }
                        
                        // "kind of" dependency injection approach: class does not rely on specific logic anymore, publishing is adjustable
                        $publisherClass = '\JobPublishers\\' . \Config::read('job_publisher');
                        $publisher = new $publisherClass();
                        
                        //Create SubQuery and assign corresponding Job to each responsible carrier
                        foreach($supportedCarriers as $cId => &$supportedCarrier) {
                            if($existingCarrier) {
                                if($cId != $existingCarrier->getId()) {
                                    continue;
                                }
                            }
                            $subQuery = new \Entities\SubQuery($publisher);
                            $subQuery->setCarrierId($supportedCarrier->getId());
                            $subQuery->setQueryId($query->getId());
                            $subQuery->setEsn($query->getEsn());
                            $subQuery->setAttempts(0);
                            if(array_key_exists($supportedCarrier->getId(), $vendorsByCarrier)) {
                                $subQuery->setVendorId($vendorsByCarrier[$supportedCarrier->getId()]->getId());
                                $subQuery->setAttempts(1);
                                //Increment Vendor stack
                                $q = \DS::getEM()->createQuery('UPDATE \Entities\Vendor v SET v.stackSize=(v.stackSize+1) WHERE v.id = :vid');
                                $q->setParameter('vid', $vendorsByCarrier[$supportedCarrier->getId()]->getId());
                                $q->execute();
                            }
                            \DS::getEM()->persist($subQuery);
                            \DS::getEM()->flush();
                            if(array_key_exists($supportedCarrier->getId(), $vendorsByCarrier)) {
                                $subQuery->publish($vendorsByCarrier[$supportedCarrier->getId()]);
                            }
                        }
                        $query->setStarted(new \DateTime());
                        \DS::getEM()->persist($query);
                        \DS::getEM()->flush();
                    } else {
                        // ... or throw \MissingParameterException to let client know that such Query already exists.
                        throw \ExceptionHandler::wrongParametersException(__('Query already exists. To override exiting query, pass parameter "force=true".'));
                    }
                } else {
                    throw \ExceptionHandler::systemFailureException(__('Data mismatch. Try again later.'));
                }
            }
        } else {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        $response = new \stdClass();
        $response->message = __('Query has been added.');
        $response->query = $query;
        
        return $response;
    }
    
    /**
     * Adds a few ESN queries as a bulk operation
     * 
     * @param string $esns comma-separated ESN numbers
     * @param integer $carrier_id the integer ID of the carrier to exclusively check ESN status on (other are skipped, default is "false") (1 - 'Verizon', 2 - 'AT&T', 3 - 'Sprint', 4 - 'T-Mobile', )
     * @param integer $user_id internal ID of the user with specified keypair - always overriden internally
     * @param boolean $force provides way to run fresh ESN verification query before the 24h period since last check passed
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     * 
     * @return array the array of responses of add() operation @see add()
     * 
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     * @throws \SystemFailureException
     * @throws \NoAccessException
     */
    public static function bulk_add($esns = false, $carrier = false, $carrier_id = false, $user_id = false, $force = false, $sealed = false){
        if(!$esns) {
            throw \ExceptionHandler::missingParameterException(__('You must specify one or few ESNs to create new query'));
        }
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to create new query'));
        }
        
        set_time_limit(0);
        
        $response = array();
        if($sealed) {
            if( $esns = (array) explode(',', $esns) ) {
                foreach($esns as $esn) {
                    $esn = trim($esn);
                    if(self::checkEsn($esn)) {
                        try {
                            $rObject = self::add($esn, $carrier, $carrier_id, $user_id, $force, $sealed);
                        } catch(\Exception $ex) {
                            $rObject = new \stdClass();
                            $rObject->message = $ex->getMessage();
                        }
                    } else {
                        $rObject = new \stdClass();
                        $rObject->message = __('Invalid ESN');
                    }
                    $response[$esn] = $rObject;
                }
            } else {
                throw \ExceptionHandler::missingParameterException(__('You must specify one or few ESNs to create new query'));
            }
        } else {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        return $response;
    }

        /**
     * Cancels the in-progress query if it is possible to do
     * 
     * @param string $esn the ESN of device to search the query for 
     * @param integer $user_id internal ID of the user with specified keypair - always overriden internally
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     * 
     * @return \stdClass standard response
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     * @throws \WrongParametersException
     */
    public static function cancel($esn = false, $user_id = false, $sealed = false) {
        \Config::write('debug', true);
        if(!$esn) {
            throw \ExceptionHandler::missingParameterException(__('You must specify ESN to cancel query'));
        }
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to cancel query'));
        }
        if(!$sealed) {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        //Find Query with specified ESN (must not be canceled/completed/failed)
        /**
         * @todo make query cancellable only by owner/SU/CLI users
         */
        $queries = \DS::getEM()->getRepository('\Entities\Query')->findBy(array('esn' => $esn, 'completed' => null, 'canceled' => null, 'failed' => null));
        if(empty($queries)) {
            throw \ExceptionHandler::wrongParametersException(__('Query does not exist or can not be canceled.'));
        } else {
            foreach($queries as $query) {
                //Find SubQueries associated with a query found and stop and update status for each
                $subQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findBy(array('queryId' => $query->getId(), 'completed' => null, 'canceled' => null, 'failed' => null));
                if(!empty($subQueries)) {
                    foreach($subQueries as &$subQuery) {
                        if($subQuery->stop()) {
                            $subQuery->setCanceled(new \DateTime());
                            $subQuery->setStatus('canceled');
                            $subQuery->setStatusDetails('Canceled by client');
                            \DS::getEM()->persist($subQuery);
                            \DS::getEM()->flush(); //important to flush right away to avoid status changes by other processes
                            //Update Vendor statistics: completed+1
                            if($vendorId = $subQuery->getVendorId()) {
                                $q = \DS::getEM()->createQuery('UPDATE \Entities\Vendor v SET v.completed=(v.completed+1) WHERE v.id = :vid');
                                $q->setParameter('vid', $vendorId);
                                $q->execute();
                            }
                        }
                    }
                }
                $query->setCanceled(new \DateTime());
                $query->setStatus('canceled');
                $query->setStatusDetails('Canceled by client');
                \DS::getEM()->persist($query);
                \DS::getEM()->flush(); //important to flush right away to avoid status changes by other processes
            }
        }
        
        $response = new \stdClass();
        $response->message = __('Query has been canceled');
        $response->query = $queries;
        
        return $response;
    }
    
    /**
     * Cancels few ESN queries as a bulk operation
     * 
     * @param string $esns comma-separated ESN numbers
     * @param integer $user_id internal ID of the user with specified keypair - always overriden internally
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     * 
     * @return array the array of responses of cancel() operation @see cancel()
     * 
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     * @throws \SystemFailureException
     * @throws \NoAccessException
     */
    public static function bulk_cancel($esns = false, $user_id = false, $sealed = false){
        if(!$esns) {
            throw \ExceptionHandler::missingParameterException(__('You must specify one or few ESNs to cancel queries'));
        }
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to cancel queries'));
        }
        
        set_time_limit(0);
        
        $response = array();
        if($sealed) {
            if( $esns = (array) explode(',', $esns) ) {
                foreach($esns as $esn) {
                    $esn = trim($esn);
                    if(self::checkEsn($esn)) {
                        try {
                            $rObject = self::cancel($esn, $user_id, $sealed);
                        } catch(\Exception $ex) {
                            $rObject = new \stdClass();
                            $rObject->message = $ex->getMessage();
                        }
                    } else {
                        $rObject = new \stdClass();
                        $rObject->message = __('Invalid ESN');
                    }
                    $response[$esn] = $rObject;
                }
            } else {
                throw \ExceptionHandler::missingParameterException(__('You must specify one or few ESNs to cancel queries'));
            }
        } else {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        return $response;
    }
    
    /**
     * Returns the full information on status of Query latest for specified ESN
     * 
     * @param string $esn the ESN of device to search the query for 
     * @param integer $user_id internal ID of the user with specified keypair - always overriden internally
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     *  
     * @return \stdClass the complete overall and per-carrier status details of ESN query
     * * 
     * <pre>
     * object(stdClass)#10 (2) {
     *     ["status"]=>
     *     string(7) "success"
     *     ["body"]=>
     *     object(stdClass)#11 (4) {
     *           ["status"]=>
     *           string(9) "completed"
     *           ["status_details"]=>
     *           object(stdClass)#12 (1) {
     *               ["gsma"]=>
     *               object(stdClass)#13 (2) {
     *                     ["status"]=>
     *                     string(5) "clean"
     *                     ["status_details"]=>
     *                     object(stdClass)#14 (16) {
     *                         ["refcode"]=>
     *                         string(14) "140XXXXXXXXX50"
     *                         ["responsestatus"]=>
     *                         string(7) "success"
     *                         ["IMEI"]=>
     *                         string(15) "352069XXXXXXX91"
     *                         ["blackliststatus"]=>
     *                         string(2) "No"
     *                         ["greyliststatus"]=>
     *                         string(2) "No"
     *                         ["imeihistory"]=>
     *                         array(1) {
     *                               [0]=>
     *                               object(stdClass)#15 (5) {
     *                                   ["action"]=>
     *                                   string(2) "NA"
     *                                   ["reason"]=>
     *                                   string(2) "NA"
     *                                   ["date"]=>
     *                                   string(2) "NA"
     *                                   ["by"]=>
     *                                   string(2) "NA"
     *                                   ["Country"]=>
     *                                   string(2) "NA"
     *                               }
     *                         }
     *                         ["manufacturer"]=>
     *                         string(9) "Apple Inc"
     *                         ["brandname"]=>
     *                         string(5) "Apple"
     *                         ["marketingname"]=>
     *                         string(22) "Apple iPhone 6 (A1586)"
     *                         ["modelname"]=>
     *                         string(16) "iPhone 6 (A1586)"
     *                         ["band"]=>
     *                         string(457) "LTE FDD 29,GSM850 (GSM800),GSM 900,GSM 1800,GSM 1900,LTE FDD BAND 1,LTE FDD BAND 2,LTE FDD BAND 3,LTE FDD BAND 4,LTE FDD BAND 5,LTE FDD BAND 7,LTE FDD BAND 8,LTE FDD BAND 13,LTE FDD BAND 17,LTE FDD BAND 18,LTE FDD BAND 19,LTE FDD BAND 20,LTE TDD BAND 38,LTE TDD BAND 39,LTE TDD BAND 40,TD-SCDMA,WCDMA FDD Band I,WCDMA FDD Band II,WCDMA FDD Band IV,WCDMA FDD Band V,WCDMA FDD Band VIII,CDMA2000,LTE FDD BAND 25,LTE FDD BAND 26,LTE FDD BAND 28,LTE TDD BAND 41"
     *                         ["operatingsys"]=>
     *                          string(3) "iOS"
     *                         ["nfc"]=>
     *                         string(3) "Yes"
     *                         ["bluetooth"]=>
     *                         string(3) "Yes"
     *                         ["WLAN"]=>
     *                         string(3) "Yes"
     *                         ["devicetype"]=>
     *                         string(10) "Smartphone"
     *                     }
     *               }
     *           }
     *           ["carriers"]=>
     *           object(stdClass)#16 (4) {
     *               ["Verizon"]=>
     *               object(stdClass)#17 (3) {
     *                     ["status"]=>
     *                     string(5) "clean"
     *                     ["status_details"]=>
     *                     string(13) "Assumed clean"
     *                     ["carrier_title"]=>
     *                     string(7) "Verizon"
     *               }
     *               ["AT&T"]=>
     *               object(stdClass)#18 (3) {
     *                     ["status"]=>
     *                     string(5) "clean"
     *                     ["status_details"]=>
     *                     object(stdClass)#19 (16) {
     *                         ["refcode"]=>
     *                         string(14) "1408XXXXXXX748"
     *                         ["responsestatus"]=>
     *                         string(7) "success"
     *                         ["IMEI"]=>
     *                         string(15) "352069XXXXXXX91"
     *                         ["blackliststatus"]=>
     *                         string(2) "No"
     *                         ["greyliststatus"]=>
     *                         string(2) "No"
     *                         ["imeihistory"]=>
     *                         array(1) {
     *                               [0]=>
     *                               object(stdClass)#20 (5) {
     *                                   ["action"]=>
     *                                   string(2) "NA"
     *                                   ["reason"]=>
     *                                   string(2) "NA"
     *                                   ["date"]=>
     *                                   string(2) "NA"
     *                                   ["by"]=>
     *                                   string(2) "NA"
     *                                   ["Country"]=>
     *                                   string(2) "NA"
     *                               }
     *                         }
     *                         ["manufacturer"]=>
     *                         string(9) "Apple Inc"
     *                         ["brandname"]=>
     *                         string(5) "Apple"
     *                         ["marketingname"]=>
     *                         string(22) "Apple iPhone 6 (A1586)"
     *                         ["modelname"]=>
     *                         string(16) "iPhone 6 (A1586)"
     *                         ["band"]=>
     *                         string(457) "LTE FDD 29,GSM850 (GSM800),GSM 900,GSM 1800,GSM 1900,LTE FDD BAND 1,LTE FDD BAND 2,LTE FDD BAND 3,LTE FDD BAND 4,LTE FDD BAND 5,LTE FDD BAND 7,LTE FDD BAND 8,LTE FDD BAND 13,LTE FDD BAND 17,LTE FDD BAND 18,LTE FDD BAND 19,LTE FDD BAND 20,LTE TDD BAND 38,LTE TDD BAND 39,LTE TDD BAND 40,TD-SCDMA,WCDMA FDD Band I,WCDMA FDD Band II,WCDMA FDD Band IV,WCDMA FDD Band V,WCDMA FDD Band VIII,CDMA2000,LTE FDD BAND 25,LTE FDD BAND 26,LTE FDD BAND 28,LTE TDD BAND 41"
     *                         ["operatingsys"]=>
     *                         string(3) "iOS"
     *                         ["nfc"]=>
     *                         string(3) "Yes"
     *                         ["bluetooth"]=>
     *                         string(3) "Yes"
     *                         ["WLAN"]=>
     *                         string(3) "Yes"
     *                         ["devicetype"]=>
     *                         string(10) "Smartphone"
     *                     }
     *                     ["carrier_title"]=>
     *                     string(4) "AT&T"
     *               }
     *               ["Sprint"]=>
     *               object(stdClass)#21 (3) {
     *                     ["status"]=>
     *                     string(5) "clean"
     *                     ["status_details"]=>
     *                     object(stdClass)#22 (16) {
     *                         ["refcode"]=>
     *                         string(14) "140820XXXXXX748"
     *                         ["responsestatus"]=>
     *                         string(7) "success"
     *                         ["IMEI"]=>
     *                         string(15) "352069XXXXXXX91"
     *                         ["blackliststatus"]=>
     *                         string(2) "No"
     *                         ["greyliststatus"]=>
     *                         string(2) "No"
     *                         ["imeihistory"]=>
     *                         array(1) {
     *                               [0]=>
     *                               object(stdClass)#23 (5) {
     *                                   ["action"]=>
     *                                   string(2) "NA"
     *                                   ["reason"]=>
     *                                   string(2) "NA"
     *                                   ["date"]=>
     *                                   string(2) "NA"
     *                                   ["by"]=>
     *                                   string(2) "NA"
     *                                   ["Country"]=>
     *                                   string(2) "NA"
     *                               }
     *                         }
     *                         ["manufacturer"]=>
     *                         string(9) "Apple Inc"
     *                         ["brandname"]=>
     *                         string(5) "Apple"
     *                         ["marketingname"]=>
     *                         string(22) "Apple iPhone 6 (A1586)"
     *                         ["modelname"]=>
     *                         string(16) "iPhone 6 (A1586)"
     *                         ["band"]=>
     *                         string(457) "LTE FDD 29,GSM850 (GSM800),GSM 900,GSM 1800,GSM 1900,LTE FDD BAND 1,LTE FDD BAND 2,LTE FDD BAND 3,LTE FDD BAND 4,LTE FDD BAND 5,LTE FDD BAND 7,LTE FDD BAND 8,LTE FDD BAND 13,LTE FDD BAND 17,LTE FDD BAND 18,LTE FDD BAND 19,LTE FDD BAND 20,LTE TDD BAND 38,LTE TDD BAND 39,LTE TDD BAND 40,TD-SCDMA,WCDMA FDD Band I,WCDMA FDD Band II,WCDMA FDD Band IV,WCDMA FDD Band V,WCDMA FDD Band VIII,CDMA2000,LTE FDD BAND 25,LTE FDD BAND 26,LTE FDD BAND 28,LTE TDD BAND 41"
     *                         ["operatingsys"]=>
     *                         string(3) "iOS"
     *                         ["nfc"]=>
     *                         string(3) "Yes"
     *                         ["bluetooth"]=>
     *                         string(3) "Yes"
     *                         ["WLAN"]=>
     *                         string(3) "Yes"
     *                         ["devicetype"]=>
     *                         string(10) "Smartphone"
     *                     }
     *                     ["carrier_title"]=>
     *                     string(6) "Sprint"
     *               }
     *               ["T-Mobile"]=>
     *               object(stdClass)#24 (3) {
     *                     ["status"]=>
     *                     string(12) "incompatible"
     *                     ["status_details"]=>
     *                     string(242) "We do not recognize the IMEI number you entered. Please try again or contact Customer Care at 1-877-453-1304.
     * 
     *                  Check out T-Mobile’s great selection of cell phones >"
     *                     ["carrier_title"]=>
     *                     string(8) "T-Mobile"
     *               }
     *           }
     *           ["device_status"]=>
     *           string(5) "clean"
     *     }
     * }
     * </pre>
     */
    public static function poll($esn = false, $user_id = false, $sealed = false) {
        
        if(!$esn) {
            throw \ExceptionHandler::missingParameterException(__('You must specify ESN to poll the query'));
        }
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to poll the query'));
        }
        if(!$sealed) {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        //poll the status of the latest query with specified ESN
        if($query = \DS::getEM()->getRepository('\Entities\Query')->findOneBy(array('esn' => $esn), array('created' => 'DESC'))) {
            return $query->statusReport();
        } else {
            throw \ExceptionHandler::wrongParametersException(__('Query does not exist.'));
        }
    }
    
    /**
     * Polls few ESN queries for their status as a bulk operation
     * 
     * @param string $esns comma-separated ESN numbers
     * @param integer $user_id internal ID of the user with specified keypair - always overriden internally
     * @param boolean $sealed specifies if API call has been properly secured by the keypair - always overridden internally by the system; means that only authorized calls are possible
     * 
     * @return array the array of responses of cancel() operation @see cancel()
     * 
     * @throws \MissingParameterException
     * @throws \WrongParametersException
     * @throws \SystemFailureException
     * @throws \NoAccessException
     */
    public static function bulk_poll($esns = false, $user_id = false, $sealed = false){
        if(!$esns) {
            throw \ExceptionHandler::missingParameterException(__('You must specify one or few ESNs to poll queries'));
        }
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to poll queries'));
        }
        
        set_time_limit(0);
        
        $response = array();
        if($sealed) {
            if( $esns = (array) explode(',', $esns) ) {
                foreach($esns as $esn) {
                    $esn = trim($esn);
                    if(self::checkEsn($esn)) {
                        try {
                            $rObject = self::poll($esn, $user_id, $sealed);
                        } catch(\Exception $ex) {
                            $rObject = new \stdClass();
                            $rObject->message = $ex->getMessage();
                        }
                    } else {
                        $rObject = new \stdClass();
                        $rObject->message = __('Invalid ESN');
                    }
                    $response[$esn] = $rObject;
                }
            } else {
                throw \ExceptionHandler::missingParameterException(__('You must specify one or few ESNs to poll queries'));
            }
        } else {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        
        return $response;
    }
    
    /**
     * Receives the callback from Vendor and updates SubQueries and Query status respectively
     * @ignore
     * 
     * @param integer $sub_query_id
     * @param string $status
     * @param string $status_details
     * @param boolean $sealed
     * @param integer $user_id
     * 
     * @return boolean
     * 
     * @throws \MissingParameterException
     * @throws \NoAccessException
     */
    public static function status_callback($sub_query_id = false, $status = false, $status_details = false, $sealed = false, $user_id = false) {
        \Logger::write('Callback received', 'custom', 'job_callbacks');
        \Logger::write(get_defined_vars(), 'custom', 'job_callbacks');
        if(!$user_id) {
            throw \ExceptionHandler::missingParameterException(__('You must be authorized to cancel query'));
        }
        if(!$sealed) {
            throw \ExceptionHandler::noAccessException(__('Access not allowed'));
        }
        /**
         * @todo Verify vendor
         */
        //Fetch respective SubQuery and update it's status
        $qb = \DS::getEM()->createQueryBuilder();
        $qb
            ->select('sq')
            ->from('Entities\SubQuery', 'sq')
            ->where('sq.id = :sub_query_id')
            ->andWhere('sq.started IS NOT NULL')
            ->andWhere('sq.completed IS NULL')
            ->andWhere('sq.canceled IS NULL')
            ->andWhere('sq.failed IS NULL')
            ->setParameter('sub_query_id', (int)$sub_query_id);
        if($subQueries = $qb->getQuery()->getResult()) {
            \Logger::write($subQueries, 'custom', 'job_callbacks');
            //Update SubQuery status accordingly if access granted
            //In fact there is only one SubQuery, loop is only to withdraw it from array returned by query
            foreach($subQueries as &$subQuery) {
                $subQuery->updateStatus($status, $status_details);
                \DS::getEM()->persist($subQuery);
                \DS::getEM()->flush();
                if(!($incompleteSubQueries = \DS::getEM()->getRepository('\Entities\SubQuery')->findBy(array('queryId' => $subQuery->getQueryId(), 'completed' => null)))) {
                    //Check Query status and update it too if this was the last SubQuery to wait => only in case of finishing of all of SubQueries
                    if($query = \DS::getEM()->getRepository('\Entities\Query')->find($subQuery->getQueryId())) {
                        $query->updateStatus();
                        \DS::getEM()->persist($query);
                        \DS::getEM()->flush();
                    }
                }
            }
            return true;
        } else {
            \Logger::write('No subqueries found', 'custom', 'job_callbacks');
            return false;
        }
    }

}

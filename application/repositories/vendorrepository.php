<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * VendorRepository
 */
class VendorRepository extends EntityRepository {
    
    /**
     * Retrieves a host name from console output text lines
     * 
     * @param array $execOutput
     * @return string
     */
    private static function parseHostname($execOutput) {
        $hostname = false;
        if (is_array($execOutput) && !empty($execOutput)) {
            $possibleHostname = false;
            foreach ($execOutput as $outputLine) {
                if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $outputLine, $m)) {
                    if (strpos($outputLine, 'Bcast') !== false) {
                        $possibleHostname = $m[0];
                        break;
                    }
                    if (strpos($outputLine, 'aws-ip') !== false) {
                        $hostname = $m[0];
                        break;
                    }
                }
            }
            if (!$hostname) {
                $hostname = $possibleHostname;
            }
        }
        
        return $hostname;
    }
    
    /**
     * Creates a new Vendor instance on request
     * 
     * @return \Entities\Vendor
     */
    public function create() {
        
        $vendor = new \Entities\Vendor();
        $vendor->setStackSize(0);
        $vendor->setCompleted(0);
        $vendor->setFailed(0);
        
        \DS::getEntityManager()->persist($vendor);
        \DS::getEntityManager()->flush();
        
        $publicKey = \PwGen::generateKey();
        $privateKey = \PwGen::generateKey(29);
       
        if(!is_readable(FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances' )) {
            mkdir(FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances');
        }
        
        $cliFunction = 'exec';
        
        //1. Init new instance via Vagrant and related features
        //The box must exist and be added to vagrant boxes list
        //1.1. Copy the vagrant_template dir into instances/[new instance ID] dir
        $execOutput = array();
        $destinationInstanceDir = FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances' . FS_DS . $vendor->getId();
        if(in_array(\Config::read('environment'), array('production', 'staging', 'test'))) {
            $res = $cliFunction('cp -r ' . FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'vagrant_template_aws ' .  $destinationInstanceDir, $execOutput);
        } else {
            $res = $cliFunction('cp -r ' . FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'vagrant_template ' .  $destinationInstanceDir, $execOutput);
        }
        
        $hostname = false;
        
        if(is_readable($destinationInstanceDir)) {
            //1.2. Update the key pair in Vagrant file
            $vagrantFileConfig = file_get_contents($destinationInstanceDir . FS_DS . 'Vagrantfile');
            $awsAmi = \Config::read('amis');
            $awsAmi = $awsAmi[rand(0, (count($awsAmi)-1))];
            $vagrantFileConfig = str_replace(
                    array(
                        'ACCESS_KEY_ID',
                        'SECRET_ACCESS_KEY',
                        'AMI_ID',
                        'REGION_ID',
                        'PUBLIC_KEY',
                        'PRIVATE_KEY',
                        'API_ENDPOINT',
                        'API_PROTOCOL',
                        'RABBITMQ_HOST',
                        'TSUNAMI_NODE_AMQP_QUEUE'), 
                    array(
                        \Config::read('aws_access_key_id'),
                        \Config::read('aws_secret_access_key'),
                        $awsAmi['ami'],
                        $awsAmi['region'],
                        $publicKey,
                        $privateKey,
                        \Config::read('host_endpoint'),
                        \Config::read('host_protocol'),
                        \Config::read('amqp_host'),
                        'jobs_v' . $vendor->getId()
                    ), $vagrantFileConfig
            );
            file_put_contents($destinationInstanceDir . FS_DS . 'Vagrantfile', $vagrantFileConfig);
            //1.3. cd instances/[new instance ID]
            //1.4. Run vagrant up
            if(in_array(\Config::read('environment'), array('production', 'staging', 'test'))) {
                $res = $cliFunction('cd ' . $destinationInstanceDir . ';vagrant up --provider=aws', $execOutput);
            } else {
                $res = $cliFunction('cd ' . $destinationInstanceDir . ';vagrant up', $execOutput);
            }
            //Parse the script output
            $hostname = self::parseHostname($execOutput);
        }
        
        
        if($hostname) {
            
            //2) Check for existing active intances with same IP address and set disabled in DB
            if($existingVendors = $this->findByHostname($hostname)) {
                foreach ($existingVendors as $existingVendor) {
                    $existingVendor->setDisabled(new \DateTime());
                    \DS::getEntityManager()->persist($existingVendor);
                }
            }

            //Add a new available (activated=YES disabled=NO) `vendors` entry
            $vendor->setActivated(new \DateTime());
            $vendor->setHostname($hostname);

            \DS::getEntityManager()->persist($vendor);
            if($user = \DS::getEntityManager()->getRepository('\Entities\User')->findByUsername($vendor->getHostname())) {
                $user->setPublicKey($publicKey);
                $user->setPrivateKey($privateKey);
            } else {
                //Respective \Entities\User creation
                $user = new \Entities\User();
                $user->setUsername($vendor->getHostname());
                $user->setPublicKey($publicKey);
                $user->setPrivateKey($privateKey);
            }
            \DS::getEntityManager()->persist($user);
            \DS::getEntityManager()->flush();

            //Find default carriers if supplied
            if(($defaultCarriers = \Config::read('default_carriers')) and $defaultCarriers = \DS::getEntityManager()->getRepository('\Entities\Carrier')->findByTitle($defaultCarriers)) {
                //Add each default carrier to the new Vendor
                foreach($defaultCarriers as &$supportedCarrier) {
                    $carrierVendor = new \Entities\CarriersVendor();
                    $carrierVendor->setCarrierId($supportedCarrier->getId());
                    $carrierVendor->setVendorId($vendor->getId());
                    \DS::getEM()->persist($carrierVendor);
                }
                \DS::getEM()->flush();
            }
        
            return $vendor;
        }
        
        return false;
    }
    
    /**
     * Activates vendor found by ID or hostname
     * 
     * @param integer|string $vendor
     * @return \Entities\Vendor
     */
    public function activate($vendor){
        
        $vendor = $this->getVendor($vendor);
        
        $instanceFunctional = false;
        if($vendor) {

            //Verify here if related instance is alive
            $apiClient = new \ApiClient(\Config::read('public_key'), \Config::read('private_key'), \Config::read('api_protocol') . '://' . $vendor->getHostname());

            $data = array(
                'service' => 'hello',
                'operation' => 'index',
                'version' => '0.1',
                'format' => 'json'
            );

            if($response = json_decode($apiClient->send($data, 'POST'))) {
                if($response->status == 'success') {
                    $instanceFunctional = true;
                }
            }

            if($instanceFunctional) {
                $vendor->setActivated(new \DateTime());
                $vendor->setDisabled(null);
            }
            \DS::getEntityManager()->persist($vendor);
            \DS::getEntityManager()->flush();
        }
        
        return $instanceFunctional;
    }

    /**
     * Disables vendor found by ID or hostname
     * 
     * @param integer|string $vendor
     * @return \Entities\Vendor
     */
    public function disable($vendor){

        $vendor = $this->getVendor($vendor);
        
        $instanceFunctional = false;
        
        if($vendor) {
            
            //disable instance right away to avoid assignment of new jobs
            $vendor->setDisabled(new \DateTime());
            \DS::getEntityManager()->persist($vendor);
            \DS::getEntityManager()->flush();
            
            //Wait while instance still has some assigned jobs in the stack
            $qb = \DS::getEM()->createQueryBuilder();
            $qb->select('v.stackSize-v.completed-v.failed')
                    ->from('Entities\Vendor', 'v')
                    ->where('v.id = ' . $vendor->id());
            
            $q = $qb->getQuery();
            
            $totalSleep = 0;
            if($countPending = $q->getSingleScalarResult()) {
                while($countPending = $q->getSingleScalarResult()) {
                    sleep(10);
                    $totalSleep += 10;
                    if($totalSleep>=120) {
                        //Exit and disable instance after 2 minutes of waiting
                        break;
                    }
                }
            }
            
            $cliFunction = 'exec';
            $execOutput = array();
            $destinationInstanceDir = FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances' . FS_DS . $vendor->getId();

            if(is_readable($destinationInstanceDir)) {
                //Run vagrant destroy to terminate instance
                $res = $cliFunction('cd ' . $destinationInstanceDir . ';vagrant halt', $execOutput);
            }
            
            //Re-enable instance in the database if disabling supposedly failed
            if(!array_key_exists(0, $execOutput) || strpos($execOutput[0], 'Stopping the instance') === false) {
                $vendor->setDisabled(null);
                \DS::getEntityManager()->persist($vendor);
                \DS::getEntityManager()->flush();
            }
            
        }
        
        return $vendor;
    }
    
    /**
     * Restarts a previously suspended AWS instance
     * 
     * @param string|integer $vendor
     * @return \Entities\Vendor
     */
    public function resume($vendor){

        $vendor = $this->getVendor($vendor);
        
        if($vendor) {
            
            //Check if instance is actually disabled (AWS instance must be stopped)
            if($this->status($vendor)!=='stopped') {
                return false;
            }
            
            $cliFunction = 'exec';
            $execOutput = array();
            $destinationInstanceDir = FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances' . FS_DS . $vendor->getId();

            $hostname = false;
            
            if(is_readable($destinationInstanceDir)) {
                
                //Run vagrant destroy to terminate instance
                $res = $cliFunction('cd ' . $destinationInstanceDir . ';vagrant up', $execOutput);
                /**
                 * Parse the CLI output and:
                 * 1) Find out the IP address
                 */
                $hostname = self::parseHostname($execOutput);
            }

            if($hostname) {
                
                //2) Check for existing active intances with same IP address and set disabled in DB
                if($existingVendors = $this->findByHostname($hostname)) {
                    foreach($existingVendors as $existingVendor) {
                        $existingVendor->setDisabled(new \DateTime());
                        \DS::getEntityManager()->persist($existingVendor);
                    }
                }

                //Add a new available (activated=YES disabled=NO) `vendors` entry
                $vendor->setActivated(new \DateTime());
                $vendor->setDisabled(null);
                
                //3) Update the IP address in instance entry
                $vendor->setHostname($hostname);

                \DS::getEntityManager()->persist($vendor);
                \DS::getEntityManager()->flush();

                return $vendor;
            }        
        }
        
        return false;
    }
    
    /**
     * 
     * 
     * @param string|integer|\Entities\Vendor $vendor
     * @return boolean
     */
    public function destroy($vendor) {
        
        $vendor = $this->getVendor($vendor);
        
        $instanceFunctional = false;
        
        if($vendor) {
            
            $cliFunction = 'exec';
            $execOutput = array();
            $destinationInstanceDir = FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances' . FS_DS . $vendor->getId();

            if(is_readable($destinationInstanceDir)) {
                
                //Run vagrant destroy to terminate instance
                $res = $cliFunction('cd ' . $destinationInstanceDir . ';vagrant destroy -f', $execOutput);
                
                /**
                 * @todo Parse the CLI output
                 */
                $instanceFunctional = true;
            }
            if($instanceFunctional) {
//                $vendor->setDisabled(new \DateTime());
                \DS::getEM()->remove($vendor);
            }
//            \DS::getEntityManager()->persist($vendor);
            \DS::getEntityManager()->flush();
        }
        
        return $instanceFunctional;
    }
    
    /**
     * Detects AWS instance status
     * 
     * @param string|integer|\Entities\Vendor $vendor
     * @return boolean
     */
    public function status($vendor) {
        
        $vendor = $this->getVendor($vendor);
        
        if($vendor) {
            
            $cliFunction = 'exec';
            $execOutput = array();
            $destinationInstanceDir = FS_ROOT . FS_DS . 'vagrant' . FS_DS . 'instances' . FS_DS . $vendor->getId();

            if(is_readable($destinationInstanceDir)) {
                
                //Run vagrant status to detect AWS instance status
                $res = $cliFunction('cd ' . $destinationInstanceDir . ';vagrant status', $execOutput);
                $execOutput = implode(PHP_EOL,  $execOutput);
                
                switch(true) {
                    case preg_match('/The\sEC2\sinstance\sis\srunning/', $execOutput):
                        return 'running';
                        break;
                    case preg_match('/The\sEC2\sinstance\sis\sstopped/', $execOutput):
                        return 'stopped';
                        break;
                    case preg_match('/The\sEC2\sinstance\sis\snot\screated/', $execOutput):
                        return 'not_created';
                        break;
                    default:
                        return 'undefined';
                        break;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Composes an array of AvailableVendor entity objects with key corresponding to carrier ID
     * 
     * @return array
     */
    public function getVendorsByCarrier() {
        
        $rsm = new ResultSetMappingBuilder(\DS::getEM());
        $rsm->addRootEntityFromClassMetadata('\Entities\AvailableVendor', 'v');

        $sql = "SELECT q.* FROM (SELECT 
    v.*, 
    cv.`carrier_id`,
    (v.`stack_size`-v.`completed`-v.`failed`) AS active_jobs
FROM 
    `carriers_vendors` cv
    RIGHT JOIN `vendors` v 
        ON v.`id` = cv.`vendor_id`
WHERE 
    v.disabled IS NULL
    AND v.activated IS NOT NULL
ORDER BY 
    (v.`stack_size`-v.`completed`-v.`failed`) ASC,
    v.`failed` ASC,
    v.`stack_size` ASC,
    RAND()) q
GROUP BY q.`carrier_id`;";
        
        $vendors = \DS::getEM()->createNativeQuery($sql, $rsm)->setHint(\Doctrine\ORM\Query::HINT_REFRESH, true)->getResult();
        
        $vendorsByCarrier = array();
        if (!empty($vendors)) {
            foreach ($vendors as $k => $v) {
                unset($vendors[$k]);
                $vendorsByCarrier[$v->getCarrierId()] = $v;
            }
        }
        
        return $vendorsByCarrier;
    }
    
    /**
     * Retrieves the Vendor using given credential
     * 
     * @return \Entities\Vendor
     */
    public function getVendor($vendor) {
        if($vendor instanceof \Entities\Vendor) {
            //Nothing!
        }elseif(preg_match('/^[0-9]+$/', $vendor)) {
            $vendor = $this->find($vendor);
        } else {
            $vendor = $this->findOneByHostname($vendor);
        }
        return $vendor;
    }
    
}

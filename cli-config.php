<?php

/**
 * Configuration file for the Doctrine CLI tools
 */
require_once 'init/common.php';

//use Doctrine\Common\Annotations\AnnotationReader;
//use Doctrine\Common\Annotations\AnnotationRegistry;
//
//
//$classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
//$classLoader->register();
//
//$classLoader = new \Doctrine\Common\ClassLoader('Entities', FS_ROOT . FS_DS . 'application' . FS_DS . 'entities');
//$classLoader->register();
//$classLoader = new \Doctrine\Common\ClassLoader('Proxies', FS_ROOT . FS_DS . 'application' . FS_DS . 'persistent');
//$classLoader->register();
//
//$config = new \Doctrine\ORM\Configuration();
//$config->setProxyDir(FS_ROOT . FS_DS . 'application' . FS_DS . 'persistent' . FS_DS . 'Proxies');
//$config->setProxyNamespace('Proxies');
//
//$config->setAutoGenerateProxyClasses(true);
//
//
// //Here is the part that needs to be adjusted to make allow the ORM namespace in the annotation be recognized
//
//#$driverImpl = $config->newDefaultAnnotationDriver(array(__DIR__ . "/application/persistent/Entities"));
//
//AnnotationRegistry::registerFile("vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
//$reader = new AnnotationReader();
//$driverImpl = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, array(FS_ROOT . FS_DS . 'application' . FS_DS . 'metadata'));
//$config->setMetadataDriverImpl($driverImpl);
//
////End of Changes
//$cache = new \Doctrine\Common\Cache\ArrayCache();
//
//$config->setMetadataCacheImpl($cache);
//$config->setQueryCacheImpl($cache);
//
//$platform = \DS::getEntityManager($config)->getConnection()->getDatabasePlatform();
//$platform->registerDoctrineTypeMapping('enum', 'string');

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
        'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper(\DS::getConnection()),
        'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(\DS::getEntityManager())
));
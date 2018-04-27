<?php

/**
 *  [DoctrineSingleton] Singleton Class which provides access point to Doctrine ORM data operations
 */
class DS {

    /**
     * EntityManager the singleton class will hold
     * 
     * @var \Doctrine\Common\EntityManager
     */
    private static $entityManager = false;
    
    /**
     * Doctrine database connection
     * 
     * @var \Doctrine\DBAL\Connection 
     */
    private static $connection = false;
    
    /**
     * Performs doctrine bootstrap configuration
     * 
     * @return \Doctrine\Common\EntityManager
     */
    public static function getEntityManager() {
        if (self::$entityManager === false) {
            $paths = array(FS_ROOT . FS_DS . 'application' . FS_DS . 'metadata');
            $config = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration($paths, false, null, null);
            //Get EntityManager instance for use in console and elsewhere via dependency injection
            self::$entityManager = \Doctrine\ORM\EntityManager::create(Config::read('database'), $config);
        }
        return self::$entityManager;
    }

    /**
     * Provide access to Doctrine DBAL database object
     * 
     * @return \Doctrine\DBAL\Connection
     */
    public static function getConnection() {
        if(self::$connection === false) {
            //Fix the problem with ENUM fields which is impossible to handle completely via Doctrine
            self::$connection = self::getEntityManager()->getConnection();
            self::$connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        }
        return self::$connection;
    }
    
    /**
     * Alias for self::getEntityManager();
     * 
     * @see self::getEntityManager()
     * 
     * @return \Doctrine\Common\EntityManager
     */
    public static function getEM() {
        return self::getEntityManager();
    }
    
}

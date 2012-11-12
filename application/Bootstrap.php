<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap{
    /**
     * generate registry
     * @return Zend_Registry
     */
    protected function _initRegistry(){
        $registry = Zend_Registry::getInstance();
        return $registry;
    }
    /**
     * Register namespace Default_
     * @return Zend_Application_Module_Autoloader
     */
    protected function _initAutoload(){
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default_',
            'basePath'  => dirname(__FILE__),
        ));
        return $autoloader;
    }       
    /**
     * Initialize Doctrine
     * @return Doctrine_Manager
     */
    public function _initDoctrine() {
        // include and register Doctrine's class loader
        require_once('Doctrine/Common/ClassLoader.php');
        
        $classLoader = new \Doctrine\Common\ClassLoader(
            'Doctrine', 
            APPLICATION_PATH . '/../library/'
        );
        $classLoader->register();
        
        // create the Doctrine configuration
        $config = new \Doctrine\ORM\Configuration();
        
        //get the config options from the application.ini file.
        $options = $this->getOptions();
        
        // setting the cache ( to ArrayCache. Take a look at
        // the Doctrine manual for different options ! )
        $cache = NULL;
        if ($options['doctrine']['cache']['apc']) {
            $cache = new \Doctrine\Common\Cache\ApcCache(); //use APC cache
        } else {
            $cache = new \Doctrine\Common\Cache\ArrayCache(); //use default cache            
        }                                
        $config->setMetadataCacheImpl($cache);
        
        $config->setEntityNamespaces(array('Entities'=>'Entities'));
        
        // choosing the driver for our database schema
        // we'll use annotations
        $driverImpl = $config->newDefaultAnnotationDriver(
            APPLICATION_PATH . '/models/Entities'
        );
        $config->setMetadataDriverImpl($driverImpl);        
        $config->setQueryCacheImpl($cache);        
        
        // set the proxy dir and set some options
        
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(APPLICATION_PATH . '/models/Proxies');
        $config->setProxyNamespace('Proxies');
        
        // now create the entity manager and use the connection
        // settings we defined in our application.ini        
        $conn = array(
            'driver'    => $options['resources']['db']['adapter'],
            'user'      => $options['resources']['db']['params']['username'],
            'password'  => $options['resources']['db']['params']['password'],
            'dbname'    => $options['resources']['db']['params']['dbname'],
            'host'      => $options['resources']['db']['params']['host']
        );                
        
        $entityManager = \Doctrine\ORM\EntityManager::create($conn, $config);
        
        // push the entity manager into our registry for later use
        Zend_Registry::set('em', $entityManager);
        
        return $entityManager;
    }
}


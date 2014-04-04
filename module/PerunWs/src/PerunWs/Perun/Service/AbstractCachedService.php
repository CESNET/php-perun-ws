<?php

namespace PerunWs\Perun\Service;

use Zend\Cache;
use Zend\Stdlib\Parameters;


abstract class AbstractCachedService
{

    /**
     * @var Cache\Pattern\ObjectCache
     */
    protected $objectCache;

    /**
     * @var ServiceInterface
     */
    protected $service;


    /**
     * Constructor.
     * 
     * @param ServiceInterface $service
     * @param Cache\Storage\StorageInterface $cacheStorage
     */
    public function __construct(ServiceInterface $service, Cache\Storage\StorageInterface $cacheStorage)
    {
        $this->service = $service;
        
        $objectCache = Cache\PatternFactory::factory('object', array(
            'object' => $service,
            'storage' => $cacheStorage
        ));
        
        $this->setObjectCache($objectCache);
    }


    /**
     * @return Parameters
     */
    public function getParameters()
    {
        return $this->service->getParameters();
    }


    /**
     * @param Parameters $parameters
     */
    public function setParameters(Parameters $parameters)
    {
        $this->service->setParameters($parameters);
    }


    /**
     * @param Cache\Pattern\ObjectCache $objectCache
     */
    public function setObjectCache($objectCache)
    {
        $this->objectCache = $objectCache;
    }


    /**
     * @return Cache\Pattern\ObjectCache
     */
    public function getObjectCache()
    {
        return $this->objectCache;
    }


    /**
     * Uniform cached call.
     * 
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function cachedCall($method, array $arguments)
    {
        return call_user_func_array(array(
            $this->objectCache,
            $method
        ), $arguments);
    }


    public function directCall($method, array $arguments)
    {
        return call_user_func_array(array(
            $this->objectCache->getOptions()->getObject(),
            $method
        ), $arguments);
    }


    public function invalidateCall($method, array $arguments)
    {
        /* @var $cacheStorage \Zend\Cache\Storage\StorageInterface */
        $cacheStorage = $this->objectCache->getOptions()->getStorage();
        $key = $this->objectCache->generateKey($method, $arguments);
        $cacheStorage->removeItem($key);
    }
}
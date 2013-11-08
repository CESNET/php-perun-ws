<?php

namespace PerunWs\Perun\Service;

use Zend\Cache;


abstract class AbstractCachedService
{

    /**
     * @var Cache\Pattern\ObjectCache
     */
    protected $objectCache;


    /**
     * Constructor.
     * 
     * @param ServiceInterface $service
     * @param Cache\Storage\StorageInterface $cacheStorage
     */
    public function __construct(ServiceInterface $service, Cache\Storage\StorageInterface $cacheStorage)
    {
        $objectCache = Cache\PatternFactory::factory('object', array(
            'object' => $service,
            'storage' => $cacheStorage
        ));
        
        $this->setObjectCache($objectCache);
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
}
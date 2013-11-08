<?php

namespace PerunWsTest\User\Service;


class AbstractCachedServiceTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructor()
    {
        $service = $this->getServiceMock();
        $cacheStorage = $this->getCacheStorageMock();
        
        $cachedService = $this->getCachedServiceMock($service, $cacheStorage);
        
        $objectCache = $cachedService->getObjectCache();
        $this->assertInstanceOf('Zend\Cache\Pattern\ObjectCache', $objectCache);
        
        $this->assertSame($service, $objectCache->getOptions()
            ->getObject());
        $this->assertSame($cacheStorage, $objectCache->getOptions()
            ->getStorage());
    }


    public function testCachedCall()
    {
        $service = $this->getServiceMock();
        $cacheStorage = $this->getCacheStorageMock();
        $method = 'foo';
        
        $arg1 = 123;
        $arg2 = 456;
        $arguments = array(
            $arg1,
            $arg2
        );
        $result = 'some return value';
        
        $objectCache = $this->getObjectCacheMock();
        $objectCache->expects($this->once())
            ->method($method)
            ->with($arg1, $arg2)
            ->will($this->returnValue($result));
        
        $cachedService = $this->getCachedServiceMock($service, $cacheStorage);
        $cachedService->setObjectCache($objectCache);
        
        $this->assertSame($result, $cachedService->cachedCall($method, $arguments));
    }
    
    /*
     * 
     */
    protected function getServiceMock()
    {
        $service = $this->getMockBuilder('PerunWs\Perun\Service\ServiceInterface')->getMock();
        return $service;
    }


    protected function getCacheStorageMock()
    {
        $cacheStorage = $this->getMock('Zend\Cache\Storage\StorageInterface');
        return $cacheStorage;
    }


    protected function getObjectCacheMock()
    {
        $objectCache = $this->getMockBuilder('Zend\Cache\Pattern\ObjectCache')
            ->setMethods(array(
            'foo'
        ))
            ->getMock();
        return $objectCache;
    }


    /**
     * @param unknown $service
     * @param unknown $cacheStorage
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCachedServiceMock($service, $cacheStorage)
    {
        $cachedService = $this->getMockBuilder('PerunWs\Perun\Service\AbstractCachedService')
            ->setConstructorArgs(array(
            $service,
            $cacheStorage
        ))
            ->getMockForAbstractClass();
        
        return $cachedService;
    }
}
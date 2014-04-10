<?php

namespace PerunWs\User\Group;

use PerunWs\Group\Service\Exception\MemberRetrievalException;


class ListenerTest extends \PHPUnit_Framework_TestCase
{


    public function testAttach()
    {
        $service = $this->createServiceMock();
        $listener = new Listener($service);
        
        $events = $this->createEventsMock();
        $events->expects($this->once())
            ->method('attach')
            ->with('fetchAll', array(
            $listener,
            'onFetchAll'
        ));
        
        $listener->attach($events);
    }


    public function testOnFetchAll()
    {
        $userId = 123;
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        
        $service = $this->createServiceMock();
        $listener = new Listener($service);
        
        $resourceEvent = $this->getMock('PhlyRestfully\ResourceEvent');
        $resourceEvent->expects($this->once())
            ->method('getRouteParam')
            ->with('user_id')
            ->will($this->returnValue($userId));
        
        $params = $this->createParametersMock();
        
        $paramsFactory = $this->createParametersFactoryMock();
        $paramsFactory->expects($this->once())
            ->method('createParameters')
            ->will($this->returnValue($params));
        $listener->setParametersFactory($paramsFactory);
        
        $service->expects($this->once())
            ->method('fetchUserGroups')
            ->with($userId, $params)
            ->will($this->returnValue($groups));
        
        $this->assertSame($groups, $listener->onFetchAll($resourceEvent));
    }


    public function testOnFetchAllWithFilterType()
    {
        $userId = 123;
        $filterType = 'foo,bar';
        $parsedFilterType = array(
            'foo',
            'bar'
        );
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        
        $service = $this->createServiceMock();
        $listener = new Listener($service);
        
        $resourceEvent = $this->getMock('PhlyRestfully\ResourceEvent');
        $resourceEvent->expects($this->once())
            ->method('getRouteParam')
            ->with('user_id')
            ->will($this->returnValue($userId));
        $resourceEvent->expects($this->once())
            ->method('getQueryParam')
            ->with('filter_type')
            ->will($this->returnValue($filterType));
        
        $csvParser = $this->getMock('PerunWs\Util\CsvParser');
        $csvParser->expects($this->once())
            ->method('parse')
            ->with($filterType)
            ->will($this->returnValue($parsedFilterType));
        $listener->setCsvParser($csvParser);
        
        $params = $this->createParametersMock();
        $params->expects($this->once())
            ->method('set')
            ->with('filter_type', $parsedFilterType);
        
        $paramsFactory = $this->createParametersFactoryMock();
        $paramsFactory->expects($this->once())
            ->method('createParameters')
            ->will($this->returnValue($params));
        $listener->setParametersFactory($paramsFactory);
        
        $service->expects($this->once())
            ->method('fetchUserGroups')
            ->with($userId, $params)
            ->will($this->returnValue($groups));
        
        $this->assertSame($groups, $listener->onFetchAll($resourceEvent));
    }
    
    /*
     * 
     */
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function createServiceMock()
    {
        $service = $this->getMockBuilder('PerunWs\Group\Service\ServiceInterface')->getMock();
        return $service;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function createEventsMock()
    {
        $events = $this->getMock('Zend\EventManager\EventManagerInterface');
        return $events;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function createParametersMock()
    {
        return $this->getMock('Zend\Stdlib\Parameters');
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function createParametersFactoryMock()
    {
        return $this->getMock('PerunWs\Util\ParametersFactory');
    }
}
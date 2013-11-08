<?php

namespace PerunWs\User\Group;

use PerunWs\Group\Service\Exception\MemberRetrievalException;


class ListenerTest extends \PHPUnit_Framework_TestCase
{


    public function testAttach()
    {
        $service = $this->getServiceMock();
        $listener = new Listener($service);
        
        $events = $this->getEventsMock();
        $events->expects($this->once())
            ->method('attach')
            ->with('fetchAll', array(
            $listener,
            'onFetchAll'
        ));
        
        $listener->attach($events);
    }


    public function testOnFetchAllWithMemberRetrievalException()
    {
        $this->setExpectedException('PhlyRestfully\Exception\RuntimeException', 'member error');
        
        $userId = 123;
        
        $service = $this->getServiceMock();
        $listener = new Listener($service);
        
        $resourceEvent = $this->getMock('PhlyRestfully\ResourceEvent');
        $resourceEvent->expects($this->once())
            ->method('getRouteParam')
            ->with('user_id')
            ->will($this->returnValue($userId));
        
        $service->expects($this->once())
            ->method('fetchUserGroups')
            ->with($userId)
            ->will($this->throwException(new MemberRetrievalException('member error')));
        
        $listener->onFetchAll($resourceEvent);
    }


    public function testOnFetchAll()
    {
        $userId = 123;
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        
        $service = $this->getServiceMock();
        $listener = new Listener($service);
        
        $resourceEvent = $this->getMock('PhlyRestfully\ResourceEvent');
        $resourceEvent->expects($this->once())
            ->method('getRouteParam')
            ->with('user_id')
            ->will($this->returnValue($userId));
        
        $service->expects($this->once())
            ->method('fetchUserGroups')
            ->with($userId)
            ->will($this->returnValue($groups));
        
        $this->assertSame($groups, $listener->onFetchAll($resourceEvent));
    }
    
    /*
     * 
     */
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getServiceMock()
    {
        $service = $this->getMockBuilder('PerunWs\Group\Service\ServiceInterface')->getMock();
        return $service;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEventsMock()
    {
        $events = $this->getMock('Zend\EventManager\EventManagerInterface');
        return $events;
    }
}
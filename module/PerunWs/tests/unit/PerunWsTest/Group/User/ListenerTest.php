<?php

namespace PerunWsTest\Group\User;

use PerunWs\Group\User\Listener;


class ListenerTest extends \PHPUnit_Framework_Testcase
{

    protected $listener;


    public function setUp()
    {
        $service = $this->getServiceMock();
        $this->listener = new Listener($service);
    }


    public function testAttach()
    {
        $listeners = array(
            'fetchAll' => 'onFetchAll',
            'update' => 'onUpdate',
            'delete' => 'onDelete'
        );
        
        $events = $this->getMock('Zend\EventManager\EventManagerInterface');
        
        $i = 0;
        foreach ($listeners as $eventName => $listenerName) {
            $events->expects($this->at($i ++))
                ->method('attach')
                ->with($eventName, array(
                $this->listener,
                $listenerName
            ));
        }
        
        $this->listener->attach($events);
    }


    public function testOnFetchAll()
    {
        $groupId = 123;
        
        $event = $this->getEventMock(array(
            'group_id' => $groupId
        ));
        
        $members = $this->getMock('InoPerunApi\Entity\Collection\MemberCollection');
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('fetchMembers')
            ->will($this->returnValue($members));
        $this->listener->setService($service);
        
        $this->assertSame($members, $this->listener->onFetchAll($event));
    }


    public function testOnUpdate()
    {
        $groupId = 123;
        $userId = 456;
        
        $data = array(
            'group_id' => $groupId,
            'user_id' => $userId
        );
        
        $event = $this->getEventMock($data);
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('addUserToGroup')
            ->with($userId, $groupId);
        $this->listener->setService($service);
        
        $resource = $this->listener->onUpdate($event);
        
        $this->assertInstanceOf('PhlyRestfully\HalResource', $resource);
        $this->assertSame($userId, $resource->id);
        $this->assertEquals($resource->resource, $data);
    }


    public function testOnDelete()
    {
        $groupId = 123;
        $userId = 456;
        
        $data = array(
            'group_id' => $groupId,
            'user_id' => $userId
        );
        
        $result = true;
        
        $event = $this->getEventMock($data);
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('removeUserFromGroup')
            ->will($this->returnValue($result));
        $this->listener->setService($service);
        
        $this->assertSame($result, $this->listener->onDelete($event));
    }
    
    /*
     * 
     */
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getServiceMock()
    {
        $service = $this->getMock('PerunWs\Group\Service\ServiceInterface');
        
        return $service;
    }


    public function getEventMock(array $routeParams = array())
    {
        $event = $this->getMock('PhlyRestfully\ResourceEvent');
        $i = 0;
        foreach ($routeParams as $key => $value) {
            $event->expects($this->at($i ++))
                ->method('getRouteParam')
                ->with($key)
                ->will($this->returnValue($value));
        }
        
        return $event;
    }
}
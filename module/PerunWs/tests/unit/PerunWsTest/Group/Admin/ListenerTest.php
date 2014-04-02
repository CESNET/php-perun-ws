<?php

namespace PerunWsTest\Group\Admin;

use PerunWs\Group\Admin\Listener;
use PerunWs\Group\Service\Exception\GroupRetrievalException;
use PerunWs\Group\Service\Exception\UserAlreadyAdminException;
use PerunWs\Group\Service\Exception\UserNotAdminException;
use PerunWs\Group\Service\Exception\GroupGenericException;


class ListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Listener
     */
    protected $listener;


    public function setUp()
    {
        $service = $this->createGroupServiceMock();
        $this->listener = new Listener($service);
    }


    public function testSetService()
    {
        $service = $this->createGroupServiceMock();
        
        $this->listener->setService($service);
        
        $this->assertSame($service, $this->readAttribute($this->listener, 'service'));
    }


    public function testGetService()
    {
        $service = $this->createGroupServiceMock();
        
        $this->listener->setService($service);
        
        $this->assertSame($service, $this->listener->getService());
    }


    public function testAttach()
    {
        $definitions = array(
            array(
                'event' => 'fetchAll',
                'call' => 'onFetchAll'
            ),
            array(
                'event' => 'update',
                'call' => 'onUpdate'
            ),
            array(
                'event' => 'delete',
                'call' => 'onDelete'
            )
        );
        $defCount = count($definitions);
        
        $events = $this->getMock('Zend\EventManager\EventManagerInterface');
        
        for ($i = 0; $i < $defCount; $i ++) {
            $def = $definitions[$i];
            $events->expects($this->at($i))
                ->method('attach')
                ->with($def['event'], array(
                $this->listener,
                $def['call']
            ));
        }
        
        $this->listener->attach($events);
        
        $this->assertCount($defCount, $this->readAttribute($this->listener, 'listeners'));
    }


    public function testOnFetchAllWithException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupRetrievalException', 'group not found', 404);
        
        $groupId = 123;
        $resourceEvent = $this->createResourceEventMock($groupId);
        $exception = new GroupRetrievalException('group not found', 404);
        
        $service = $this->createGroupServiceMock();
        $service->expects($this->once())
            ->method('fetchGroupAdmins')
            ->with($groupId)
            ->will($this->throwException($exception));
        $this->listener->setService($service);
        
        $this->listener->onFetchAll($resourceEvent);
    }


    public function testOnFetchAll()
    {
        $groupId = 123;
        $resourceEvent = $this->createResourceEventMock($groupId);
        $admins = $this->createUserCollectionMock();
        
        $service = $this->createGroupServiceMock();
        $service->expects($this->once())
            ->method('fetchGroupAdmins')
            ->with($groupId)
            ->will($this->returnValue($admins));
        $this->listener->setService($service);
        
        $this->assertSame($admins, $this->listener->onFetchAll($resourceEvent));
    }


    public function testOnUpdateWithPerunException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupGenericException', 'perun error', 400);
        
        $groupId = 123;
        $userId = 456;
        $resourceEvent = $this->createResourceEventMock($groupId, $userId);
        $exception = new GroupGenericException('perun error', 400);
        
        $service = $this->createGroupServiceMock();
        $service->expects($this->once())
            ->method('addGroupAdmin')
            ->with($groupId, $userId)
            ->will($this->throwException($exception));
        $this->listener->setService($service);
        
        $this->listener->onUpdate($resourceEvent);
    }


    public function testOnUpdate()
    {
        $groupId = 123;
        $userId = 456;
        $resourceEvent = $this->createResourceEventMock($groupId, $userId);
        
        $service = $this->createGroupServiceMock();
        $service->expects($this->once())
            ->method('addGroupAdmin')
            ->with($groupId, $userId);
        $this->listener->setService($service);
        
        $resource = $this->listener->onUpdate($resourceEvent);
        
        $this->assertInstanceOf('PhlyRestfully\HalResource', $resource);
        $this->assertSame($userId, $resource->id);
    }


    public function testOnDeleteWithPerunException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupGenericException', 'perun error', 400);
        
        $groupId = 123;
        $userId = 456;
        $resourceEvent = $this->createResourceEventMock($groupId, $userId);
        $exception = new GroupGenericException('perun error', 400);
        
        $service = $this->createGroupServiceMock();
        $service->expects($this->once())
            ->method('removeGroupAdmin')
            ->with($groupId, $userId)
            ->will($this->throwException($exception));
        $this->listener->setService($service);
        
        $this->listener->onDelete($resourceEvent);
    }


    public function testOnDelete()
    {
        $groupId = 123;
        $userId = 456;
        $resourceEvent = $this->createResourceEventMock($groupId, $userId);
        
        $service = $this->createGroupServiceMock();
        $service->expects($this->once())
            ->method('removeGroupAdmin')
            ->with($groupId, $userId);
        $this->listener->setService($service);
        
        $this->assertTrue($this->listener->onDelete($resourceEvent));
    }
    
    /*
     * 
     */
    protected function createGroupServiceMock()
    {
        $service = $this->getMockBuilder('PerunWs\Group\Service\ServiceInterface')->getMock();
        
        return $service;
    }


    protected function createResourceEventMock($groupId = null, $userId = null)
    {
        $event = $this->getMock('PhlyRestfully\ResourceEvent');
        if (null !== $groupId) {
            $event->expects($this->at(0))
                ->method('getRouteParam')
                ->with('group_id')
                ->will($this->returnValue($groupId));
            
            if (null !== $userId) {
                $event->expects($this->at(1))
                    ->method('getRouteParam')
                    ->with('user_id')
                    ->will($this->returnValue($userId));
            }
        }
        
        return $event;
    }


    protected function createUserCollectionMock()
    {
        $users = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        
        return $users;
    }
}
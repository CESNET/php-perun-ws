<?php

namespace PerunWsTest\Group;

use Zend\Stdlib\Parameters;
use PerunWs\Group\Listener;


class ListenerTest extends \PHPUnit_Framework_TestCase
{

    protected $listener;


    public function setUp()
    {
        $this->listener = new Listener($this->getMock('PerunWs\Group\Service\ServiceInterface'));
    }


    public function testAttach()
    {
        $listeners = array(
            'fetch' => 'onFetch',
            'fetchAll' => 'onFetchAll',
            'create' => 'onCreate',
            'patch' => 'onPatch',
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


    public function testOnFetchWithNoId()
    {
        $this->setExpectedException('PhlyRestfully\Exception\InvalidArgumentException', null, 400);
        
        $event = $this->createEventMock();
        $this->listener->onFetch($event);
    }


    public function testOnFetchWithNotFound()
    {
        $this->setExpectedException('PhlyRestfully\Exception\DomainException', null, 404);
        
        $id = 123;
        
        $event = $this->createEventMock($id);
        $service = $this->getMock('PerunWs\Group\Service\ServiceInterface');
        $service->expects($this->once())
            ->method('fetch')
            ->with($id)
            ->will($this->returnValue(null));
        
        $this->listener->setService($service);
        
        $this->listener->onFetch($event);
    }


    public function testOnFetchWithGroupFound()
    {
        $id = 123;
        $group = $this->getMock('InoPerunApi\Entity\Group');
        
        $event = $this->createEventMock($id);
        $service = $this->createServiceMock();
        $service->expects($this->once())
            ->method('fetch')
            ->with($id)
            ->will($this->returnValue($group));
        
        $this->listener->setService($service);
        
        $this->assertSame($group, $this->listener->onFetch($event));
    }


    public function testOnFetchAll()
    {
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        
        $params = $this->createParametersMock();
        $paramsFactory = $this->createParametersFactoryMock($params);
        $this->listener->setParametersFactory($paramsFactory);
        
        $event = $this->createEventMock();
        $service = $this->createServiceMock();
        $service->expects($this->once())
            ->method('fetchAll')
            ->with($params)
            ->will($this->returnValue($groups));
        $this->listener->setService($service);
        
        $this->assertSame($groups, $this->listener->onFetchAll($event));
    }


    public function testOnFetchAllWithInvalidGroupIdFilter()
    {
        $this->setExpectedException('PhlyRestfully\Exception\InvalidArgumentException', 'Invalid value');
        
        $groupIdParam = '111,222';
        
        $resourceEvent = $this->createResourceEventMock();
        $resourceEvent->expects($this->once())
            ->method('getQueryParam')
            ->with('filter_group_id')
            ->will($this->returnValue($groupIdParam));
        
        $csvParser = $this->createCsvParserMock();
        $csvParser->expects($this->once())
            ->method('parse')
            ->with($groupIdParam)
            ->will($this->throwException(new \InvalidArgumentException('Invalid value')));
        
        $this->listener->setCsvParser($csvParser);
        
        $this->listener->onFetchAll($resourceEvent);
    }


    public function testOnFetchAllWithGroupIdFilter()
    {
        $groupIdParam = '111,222';
        $groupIdParsed = array(
            111,
            222
        );
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        
        $params = $this->createParametersMock();
        $params->expects($this->once())
            ->method('set')
            ->with('filter_group_id', $groupIdParsed);
        $paramsFactory = $this->createParametersFactoryMock($params);
        $this->listener->setParametersFactory($paramsFactory);
        
        $resourceEvent = $this->createResourceEventMock();
        $resourceEvent->expects($this->once())
            ->method('getQueryParam')
            ->with('filter_group_id')
            ->will($this->returnValue($groupIdParam));
        
        $csvParser = $this->createCsvParserMock();
        $csvParser->expects($this->once())
            ->method('parse')
            ->with($groupIdParam)
            ->will($this->returnValue($groupIdParsed));
        $this->listener->setCsvParser($csvParser);
        
        $service = $this->createServiceMock();
        $service->expects($this->once())
            ->method('fetchAll')
            ->with($params)
            ->will($this->returnValue($groups));
        $this->listener->setService($service);
        
        $this->assertSame($groups, $this->listener->onFetchAll($resourceEvent));
    }


    public function testOnCreate()
    {
        $data = array(
            'foo' => 'bar'
        );
        $group = $this->getMock('InoPerunApi\Entity\Group');
        
        $event = $this->createEventMock(null, $data);
        
        $service = $this->createServiceMock();
        $service->expects($this->once())
            ->method('create')
            ->with($data)
            ->will($this->returnValue($group));
        $this->listener->setService($service);
        
        $this->assertSame($group, $this->listener->onCreate($event));
    }


    public function testOnPatch()
    {
        $id = 123;
        $data = array(
            'foo' => 'bar'
        );
        
        $group = $this->getMock('InoPerunApi\Entity\Group');
        
        $event = $this->createEventMock($id, $data);
        
        $service = $this->createServiceMock();
        $service->expects($this->once())
            ->method('patch')
            ->with($id, $data)
            ->will($this->returnValue($group));
        $this->listener->setService($service);
        
        $this->assertSame($group, $this->listener->onPatch($event));
    }


    public function testOnDelete()
    {
        $id = 123;
        $result = true;
        
        $event = $this->createEventMock($id);
        
        $service = $this->createServiceMock();
        $service->expects($this->once())
            ->method('delete')
            ->with($id)
            ->will($this->returnValue($result));
        $this->listener->setService($service);
        
        $this->assertSame($result, $this->listener->onDelete($event));
    }
    
    /*
     * 
     */
    protected function createEventMock($id = null, $data = null)
    {
        $event = $this->getMock('PhlyRestfully\ResourceEvent');
        $i = 0;
        if ($id) {
            $event->expects($this->at($i ++))
                ->method('getParam')
                ->with('id')
                ->will($this->returnValue($id));
        }
        if ($data) {
            $event->expects($this->at($i ++))
                ->method('getParam')
                ->with('data')
                ->will($this->returnValue($data));
        }
        
        return $event;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createServiceMock()
    {
        $service = $this->getMock('PerunWs\Group\Service\ServiceInterface');
        return $service;
    }


    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createResourceEventMock()
    {
        $resourceEvent = $this->getMockBuilder('PhlyRestfully\ResourceEvent')->getMock();
        return $resourceEvent;
    }


    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCsvParserMock()
    {
        $csvParser = $this->getMockBuilder('PerunWs\Util\CsvParser')->getMock();
        return $csvParser;
    }


    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParametersMock()
    {
        $params = $this->getMock('Zend\Stdlib\Parameters');
        
        return $params;
    }


    /**
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParametersFactoryMock($params = null, $inputParams = array())
    {
        $factory = $this->getMock('PerunWs\Util\ParametersFactory');
        if ($params) {
            $factory->expects($this->once())
                ->method('createParameters')
                ->with($inputParams)
                ->will($this->returnValue($params));
        }
        return $factory;
    }
}
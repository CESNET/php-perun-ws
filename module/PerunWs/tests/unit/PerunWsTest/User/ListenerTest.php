<?php

namespace PerunWsTest\User;

use PerunWs\User\Listener;


class ListenerTest extends \PHPUnit_Framework_TestCase
{

    protected $listener;


    public function setUp()
    {
        $service = $this->getServiceMock();
        $this->listener = new Listener($service);
    }


    public function testAttach()
    {
        $eventSettings = array(
            'fetch' => 'onFetch',
            'fetchAll' => 'onFetchAll'
        );
        
        $events = $this->getEventsMock();
        
        $i = 0;
        foreach ($eventSettings as $eventName => $callbackName) {
            $events->expects($this->at($i ++))
                ->method('attach')
                ->with($eventName, array(
                $this->listener,
                $callbackName
            ));
        }
        
        $this->listener->attach($events);
    }


    public function testOnFetchWithNotFound()
    {
        $this->setExpectedException('PhlyRestfully\Exception\DomainException', null, 404);
        
        $id = 123;
        
        $resourceEvent = $this->getResourceEventMock();
        $resourceEvent->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->will($this->returnValue($id));
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('fetch')
            ->with($id)
            ->will($this->returnValue(null));
        
        $this->listener->setService($service);
        $this->listener->onFetch($resourceEvent);
    }


    public function testOnFetch()
    {
        $id = 123;
        $user = $this->getMock('InoPerunApi\Entity\User');
        
        $resourceEvent = $this->getResourceEventMock();
        $resourceEvent->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->will($this->returnValue($id));
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('fetch')
            ->with($id)
            ->will($this->returnValue($user));
        
        $this->listener->setService($service);
        $this->assertSame($user, $this->listener->onFetch($resourceEvent));
    }


    public function testOnFetchAll()
    {
        $userCollection = $this->getUserCollectionMock();
        $resourceEvent = $this->getResourceEventMock();
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($userCollection));
        
        $this->listener->setService($service);
        $this->assertSame($userCollection, $this->listener->onFetchAll($resourceEvent));
    }


    /**
     * @dataProvider dataProviderSearchStrings
     * @param unknown $searchString
     * @param string $exceptionName
     */
    public function testOnfetchAlllWithSearchString($searchString, $exceptionName = null, $exceptionString = null, $exceptionCode = null)
    {
        if (null !== $exceptionName) {
            $this->setExpectedException($exceptionName, $exceptionString, $exceptionCode);
        }
        
        $userCollection = $this->getUserCollectionMock();
        
        $resourceEvent = $this->getResourceEventMock();
        $resourceEvent->expects($this->at(0))
            ->method('getQueryParam')
            ->with('search')
            ->will($this->returnValue($searchString));
        
        if (null === $exceptionName) {
            $service = $this->getServiceMock();
            $service->expects($this->once())
                ->method('fetchAll')
                ->with(array(
                'searchString' => $searchString
            ))
                ->will($this->returnValue($userCollection));
            $this->listener->setService($service);
        }
        
        $this->assertSame($userCollection, $this->listener->onFetchAll($resourceEvent));
    }


    public function testOnFetchWithUserIdParamWithInvalidValue()
    {
        $this->setExpectedException('PhlyRestfully\Exception\InvalidArgumentException', "Invalid input value", 400);
        
        $userIdParam = '123,invalid, 789 ,007';
        
        $resourceEvent = $this->getResourceEventMock();
        $resourceEvent->expects($this->at(1))
            ->method('getQueryParam')
            ->with('filter_user_id')
            ->will($this->returnValue($userIdParam));
        
        $this->listener->onFetchAll($resourceEvent);
    }


    public function testOnFetchWithUserIdParam()
    {
        $userIdParam = '123,456, 789 ,007';
        $userIdList = array(
            123,
            456,
            789,
            7
        );
        $userCollection = $this->getUserCollectionMock();
        
        $resourceEvent = $this->getResourceEventMock();
        $resourceEvent->expects($this->at(1))
            ->method('getQueryParam')
            ->with('filter_user_id')
            ->will($this->returnValue($userIdParam));
        
        $service = $this->getServiceMock();
        $service->expects($this->once())
            ->method('fetchAll')
            ->with(array(
            'filter_user_id' => $userIdList
        ))
            ->will($this->returnValue($userCollection));
        $this->listener->setService($service);
        
        $this->assertSame($userCollection, $this->listener->onFetchAll($resourceEvent));
    }
    
    /*
     * 
     */
    protected function getServiceMock()
    {
        $service = $this->getMockBuilder('PerunWs\User\Service\ServiceInterface')->getMock();
        return $service;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventsMock()
    {
        $events = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')->getMock();
        return $events;
    }


    /**
     * 
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResourceEventMock()
    {
        $resourceEvent = $this->getMockBuilder('PhlyRestfully\ResourceEvent')->getMock();
        return $resourceEvent;
    }


    protected function getUserCollectionMock()
    {
        $userCollection = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        return $userCollection;
    }


    public function dataProviderSearchStrings()
    {
        return array(
            array(
                'search' => 'validstring',
                'exception' => null
            ),
            array(
                'search' => 'Valid String',
                'exception' => null
            ),
            array(
                'search' => 'Another Valid String',
                'exception' => null
            ),
            array(
                'search' => 'Invalid string $#%',
                'exceptionName' => 'PhlyRestfully\Exception\InvalidArgumentException',
                'exceptionString' => 'Invalid search string',
                'exceptionCode' => 400
            )
        );
    }
}
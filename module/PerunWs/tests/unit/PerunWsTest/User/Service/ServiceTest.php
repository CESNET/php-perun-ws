<?php

namespace PerunWsTest\User\Service;

use PerunWs\User\Service\Service;
use InoPerunApi\Manager\Exception\PerunErrorException;
use Zend\Stdlib\Parameters;


class ServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $service;


    public function setUp()
    {
        $parameters = $this->getMock('Zend\Stdlib\Parameters');
        $parameters->expects($this->any())
            ->method('get')
            ->with('principal_names_attribute_name')
            ->will($this->returnValue('principalNames'));
        
        $this->service = new Service($parameters);
    }


    public function testGetUsersManagerWithImplicitValue()
    {
        $managerName = 'fooManager';
        $manager = $this->getManagerMock();
        
        $service = $this->getServiceMockWithManagerName($managerName, $manager);
        $service->setUsersManagerName($managerName);
        
        $this->assertSame($manager, $service->getUsersManager());
    }


    public function testGetMembersManagerWithImplicitValue()
    {
        $managerName = 'barManager';
        $manager = $this->getManagerMock();
        
        $service = $this->getServiceMockWithManagerName($managerName, $manager);
        $service->setMembersManagerName($managerName);
        
        $this->assertSame($manager, $service->getMembersManager());
    }


    public function testFetchWithMemberNotExistsException()
    {
        $id = 123;
        
        $voId = 123;
        $serviceParams = array(
            'vo_id' => $voId
        );
        $this->service->setParameters(new Parameters($serviceParams));
        
        $callParams = array(
            'user' => $id,
            'vo' => $voId
        );
        
        $exception = new PerunErrorException();
        $exception->setErrorName(Service::PERUN_EXCEPTION_MEMBER_NOT_EXISTS);
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getMemberByUser')
            ->with($callParams)
            ->will($this->throwException($exception));
        
        $this->service->setMembersManager($manager);
        $this->assertNull($this->service->fetch($id));
    }


    public function testFetchWithGeneralException()
    {
        $this->setExpectedException('InoPerunApi\Manager\Exception\PerunErrorException');
        
        $id = 123;
        
        $voId = 123;
        $serviceParams = array(
            'vo_id' => $voId
        );
        $this->service->setParameters(new Parameters($serviceParams));
        
        $callParams = array(
            'user' => $id,
            'vo' => $voId
        );
        
        $exception = new PerunErrorException();
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getMemberByUser')
            ->with($callParams)
            ->will($this->throwException($exception));
        
        $this->service->setMembersManager($manager);
        $this->service->fetch($id);
    }


    public function testFetch()
    {
        $userId = 123;
        $memberId = 456;
        $voId = 789;
        
        $params = array(
            'user' => $userId
        );
        $user = $this->getUserMock();
        
        $member = $this->getMemberMock($memberId);
        $richMember = $this->getRichMemberMock();
        
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getMemberByUser')
            ->with(array(
            'vo' => $voId,
            'user' => $userId
        ))
            ->will($this->returnValue($member));
        
        $manager->expects($this->once())
            ->method('getRichMemberWithAttributes')
            ->with(array(
            'id' => $memberId
        ))
            ->will($this->returnValue($richMember));
        
        $this->service->setMembersManager($manager);
        
        $this->assertSame($richMember, $this->service->fetch($userId));
    }


    public function testFetchAll()
    {
        $voId = 123;
        $serviceParams = array(
            'vo_id' => $voId
        );
        $callParams = array(
            'vo' => $voId
        );
        $users = $this->getUserCollectionMock();
        
        $this->service->setParameters(new Parameters($serviceParams));
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getRichMembersWithAttributes')
            ->with($callParams)
            ->will($this->returnValue($users));
        $this->service->setMembersManager($manager);
        
        $this->assertSame($users, $this->service->fetchAll());
    }


    public function testFetchAllWithSearchString()
    {
        $voId = 123;
        $searchString = 'foo';
        $serviceParams = array(
            'vo_id' => $voId
        );
        
        $callParams = array(
            'vo' => $voId,
            'searchString' => $searchString
        );
        $users = $this->getUserCollectionMock();
        
        $this->service->setParameters(new Parameters($serviceParams));
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('findRichMembersWithAttributesInVo')
            ->with($callParams)
            ->will($this->returnValue($users));
        $this->service->setMembersManager($manager);
        
        $this->assertSame($users, $this->service->fetchAll(array(
            'searchString' => $searchString
        )));
    }


    public function testFetchByAllWithFilterUserId()
    {
        $voId = 123;
        $serviceParams = array(
            'vo_id' => $voId
        );
        
        $params = array(
            'filter_user_id' => array(
                123,
                456
            )
        );
        $users = $this->getUserCollectionMock();
        
        $service = $this->getMockBuilder('PerunWs\User\Service\Service')
            ->setConstructorArgs(array(
            new Parameters($serviceParams)
        ))
            ->setMethods(array(
            'fetchByMultipleId'
        ))
            ->getMock();
        $service->expects($this->once())
            ->method('fetchByMultipleId')
            ->with($params['filter_user_id'])
            ->will($this->returnValue($users));
        
        $this->assertSame($users, $service->fetchAll($params));
    }


    public function testFetchByPrincipalNameWithNullResult()
    {
        $principalName = 'foo';
        $params = array(
            'attributeName' => $this->service->getPrincipalNamesAttributeName(),
            'attributeValue' => $principalName
        );
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getUsersByAttributeValue')
            ->with($params)
            ->will($this->returnValue(null));
        $this->service->setUsersManager($manager);
        
        $this->assertNull($this->service->fetchByPrincipalName($principalName));
    }


    public function testFetchByPrincipalNameWithEmptyCollection()
    {
        $principalName = 'foo';
        $params = array(
            'attributeName' => $this->service->getPrincipalNamesAttributeName(),
            'attributeValue' => $principalName
        );
        $users = $this->getUserCollectionMock();
        $users->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getUsersByAttributeValue')
            ->with($params)
            ->will($this->returnValue($users));
        $this->service->setUsersManager($manager);
        
        $this->assertNull($this->service->fetchByPrincipalName($principalName));
    }


    public function testFetchByPrincipalNameWithMultipleFound()
    {
        $this->setExpectedException('PerunWs\User\Service\Exception\MultipleUsersPerPrincipalNameException');
        
        $principalName = 'foo';
        $params = array(
            'attributeName' => $this->service->getPrincipalNamesAttributeName(),
            'attributeValue' => $principalName
        );
        $users = $this->getUserCollectionMock();
        $users->expects($this->any())
            ->method('count')
            ->will($this->returnValue(2));
        
        $manager = $this->getManagerMock();
        $manager->expects($this->once())
            ->method('getUsersByAttributeValue')
            ->with($params)
            ->will($this->returnValue($users));
        $this->service->setUsersManager($manager);
        
        $this->service->fetchByPrincipalName($principalName);
    }


    public function testFetchByMultipleId()
    {
        $userIdList = array(
            123,
            456,
            789
        );
        
        $members = array(
            $this->getRichMemberMock(),
            null,
            $this->getRichMemberMock()
        );
        
        $voId = 123;
        $serviceParams = array(
            'vo_id' => $voId
        );
        
        $service = $this->getMockBuilder('PerunWs\User\Service\Service')
            ->setConstructorArgs(array(
            new Parameters($serviceParams)
        ))
            ->setMethods(array(
            'fetch'
        ))
            ->getMock();
        
        foreach ($userIdList as $index => $userId) {
            $service->expects($this->at($index))
                ->method('fetch')
                ->with($userId)
                ->will($this->returnValue($members[$index]));
        }
        
        $memberCollection = $service->fetchByMultipleId($userIdList);
        
        $this->assertCount(2, $memberCollection);
        $this->assertSame($members[0], $memberCollection->getAt(0));
        $this->assertSame($members[2], $memberCollection->getAt(1));
    }
    
    /*
     * 
     */
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getManagerMock()
    {
        $manager = $this->getMockBuilder('InoPerunApi\Manager\GenericManager')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'getRichUserWithAttributes',
            'getRichMembersWithAttributes',
            'findRichMembersWithAttributesInVo',
            'getUsersByAttributeValue',
            'getMemberByUser',
            'getRichMemberWithAttributes'
        ))
            ->getMock();
        return $manager;
    }


    protected function getServiceMockWithManagerName($managerName, $manager)
    {
        $service = $this->getMockBuilder('PerunWs\User\Service\Service')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'createManager'
        ))
            ->getMock();
        
        $service->expects($this->once())
            ->method('createManager')
            ->with($managerName)
            ->will($this->returnValue($manager));
        
        return $service;
    }


    protected function getUserMock()
    {
        $user = $this->getMock('InoPerunApi\Entity\User');
        return $user;
    }


    protected function getUserCollectionMock()
    {
        $userCollection = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        return $userCollection;
    }


    protected function getMemberMock($id = null)
    {
        $member = $this->getMockBuilder('InoPerunApi\Entity\Member')
            ->setMethods(array(
            'getId'
        ))
            ->getMock();
        if ($id) {
            $member->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($id));
        }
        
        return $member;
    }


    protected function getRichMemberMock()
    {
        $richMember = $this->getMock('InoPerunApi\Entity\RichMember');
        return $richMember;
    }


    protected function getRichMemberCollectionMock()
    {
        $richMemberCollection = $this->getMock('InoPerunApi\Entity\Collection\RichMemberCollection');
        return $richMemberCollection;
    }
}
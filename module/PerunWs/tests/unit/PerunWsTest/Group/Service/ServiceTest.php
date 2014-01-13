<?php

namespace PerunWsTest\Group\Service;

use Zend\Stdlib\Parameters;
use PerunWs\Group\Service\Service;
use InoPerunApi\Manager\Exception\PerunErrorException;


class ServiceTest extends \PHPUnit_Framework_Testcase
{

    protected $service;


    public function setUp()
    {
        $this->service = new Service($this->getMock('Zend\Stdlib\Parameters'));
    }


    public function testGetManagerWithImplicitValue()
    {
        $groupsManagerName = 'foo';
        $membersManagerName = 'bar';
        $groupsManager = $this->getManagerMock();
        $membersManager = $this->getManagerMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'createManager'
        ))
            ->getMock();
        
        $service->expects($this->at(0))
            ->method('createManager')
            ->with($groupsManagerName)
            ->will($this->returnValue($groupsManager));
        
        $service->expects($this->at(1))
            ->method('createManager')
            ->with($membersManagerName)
            ->will($this->returnValue($membersManager));
        
        $service->setGroupsManagerName($groupsManagerName);
        $service->setMembersManagerName($membersManagerName);
        
        $this->assertSame($groupsManager, $service->getGroupsManager());
        $this->assertSame($membersManager, $service->getMembersManager());
    }


    public function testGetEntityFactoryWithImplicitValue()
    {
        $factory = $this->service->getEntityFactory();
        $this->assertInstanceOf('InoPerunApi\Entity\Factory\GenericFactory', $factory);
    }


    public function testFetchAll()
    {
        $voId = 123;
        $params = array(
            'vo_id' => $voId
        );
        $groups = $this->getGroupsCollectionMock();
        
        $this->service->setParameters(new Parameters($params));
        
        $groupsManager = $this->getManagerMock(array(
            'getGroups'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroups')
            ->with(array(
            'vo' => $voId
        ))
            ->will($this->returnValue($groups));
        
        $this->service->setGroupsManager($groupsManager);
        $this->assertSame($groups, $this->service->fetchAll());
    }


    public function testFetchAllWithFilterGroupId()
    {
        $voId = 123;
        $groupIdList = array(
            111,
            222,
            333
        );
        
        $groups = $this->getGroupsCollectionMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'fetchByMultipleId',
            'getVoId'
        ))
            ->getMock();
        $service->expects($this->once())
            ->method('fetchByMultipleId')
            ->with($groupIdList)
            ->will($this->returnValue($groups));
        $service->expects($this->once())
            ->method('getVoId')
            ->will($this->returnValue($voId));
        
        $this->assertSame($groups, $service->fetchAll(array(
            'filter_group_id' => $groupIdList
        )));
    }


    public function testFetch()
    {
        $id = 123;
        $group = $this->getGroupMock();
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupById'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->returnValue($group));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($group, $this->service->fetch($id));
    }


    public function testFetchWithGeneralException()
    {
        $this->setExpectedException('Exception', 'general exception');
        
        $id = 123;
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupById'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->throwException(new \Exception('general exception')));
        
        $this->service->setGroupsManager($groupsManager);
        $this->service->fetch($id);
    }


    public function testFetchWithNotExistsException()
    {
        $id = 123;
        
        $exception = new PerunErrorException('perun error');
        $exception->setErrorName(Service::PERUN_EXCEPTION_GROUP_NOT_EXISTS);
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupById'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->throwException($exception));
        
        $this->service->setGroupsManager($groupsManager);
        $this->assertNull($this->service->fetch($id));
    }


    public function testCreateWithNoName()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupCreationException', "Missing field 'name'");
        
        $data = new \stdClass();
        $this->service->create($data);
    }


    public function testCreate()
    {
        $voId = 123;
        $data = new \stdClass();
        
        $data->name = 'foo';
        $data->description = 'bar';
        
        $group = $this->getGroupMock();
        $newGroup = $this->getGroupMock();
        
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $entityFactory = $this->getEntityFactoryMock();
        $entityFactory->expects($this->once())
            ->method('createEntityWithName')
            ->with('Group', array(
            'name' => $data->name,
            'description' => $data->description
        ))
            ->will($this->returnValue($group));
        $this->service->setEntityFactory($entityFactory);
        
        $groupsManager = $this->getManagerMock(array(
            'createGroup'
        ));
        $groupsManager->expects($this->once())
            ->method('createGroup')
            ->with(array(
            'vo' => $voId,
            'group' => $group
        ))
            ->will($this->returnValue($newGroup));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($newGroup, $this->service->create($data));
    }
    
    /*
    public function testPatch() {}
    */
    public function testDelete()
    {
        $id = 123;
        
        $groupsManager = $this->getManagerMock(array(
            'deleteGroup'
        ));
        $groupsManager->expects($this->once())
            ->method('deleteGroup')
            ->with(array(
            'group' => $id
        ));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->delete($id);
    }


    public function testFetchMembers()
    {
        $id = 123;
        $users = $this->getUserCollectionMock();
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupRichMembers'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupRichMembers')
            ->with(array(
            'group' => $id
        ))
            ->will($this->returnValue($users));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($users, $this->service->fetchMembers($id));
    }


    public function testFetchMembersWithGeneralException()
    {
        $this->setExpectedException('Exception', 'general exception');
        
        $id = 123;
        
        $exception = new \Exception('general exception');
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupRichMembers'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupRichMembers')
            ->with(array(
            'group' => $id
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->fetchMembers($id);
    }


    public function testFetchMembersWithNotExistsException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupRetrievalException');
        
        $id = 123;
        
        $exception = new PerunErrorException();
        $exception->setErrorName(Service::PERUN_EXCEPTION_GROUP_NOT_EXISTS);
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupRichMembers'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupRichMembers')
            ->with(array(
            'group' => $id
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->fetchMembers($id);
    }


    public function testFetchUserGroups()
    {
        $userId = 123;
        $voId = 456;
        $groupId = 789;
        $memberId = 12;
        
        $member = $this->getMemberMock($memberId);
        
        $groups = $this->getGroupsCollectionMock();
        
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $membersManager = $this->getManagerMock(array(
            'getMemberByUser'
        ));
        $membersManager->expects($this->once())
            ->method('getMemberByUser')
            ->with(array(
            'vo' => $voId,
            'user' => $userId
        ))
            ->will($this->returnValue($member));
        $this->service->setMembersManager($membersManager);
        
        $groupsManager = $this->getManagerMock(array(
            'getAllMemberGroups'
        ));
        $groupsManager->expects($this->once())
            ->method('getAllMemberGroups')
            ->with(array(
            'member' => $memberId
        ))
            ->will($this->returnValue($groups));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($groups, $this->service->fetchUserGroups($userId, $groupId));
    }


    public function testAddUserToGroup()
    {
        $userId = 123;
        $memberId = 456;
        $groupId = 789;
        
        $member = $this->getMemberMock($memberId);
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'getMemberByUser'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('getMemberByUser')
            ->with($userId)
            ->will($this->returnValue($member));
        
        $groupsManager = $this->getManagerMock(array(
            'addMember'
        ));
        $groupsManager->expects($this->once())
            ->method('addMember')
            ->with(array(
            'group' => $groupId,
            'member' => $memberId
        ));
        $service->setGroupsManager($groupsManager);
        
        $this->assertSame($member, $service->addUserToGroup($userId, $groupId));
    }


    public function testRemoveUserFromGroup()
    {
        $userId = 123;
        $memberId = 456;
        $groupId = 789;
        
        $member = $this->getMemberMock($memberId);
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'getMemberByUser'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('getMemberByUser')
            ->with($userId)
            ->will($this->returnValue($member));
        
        $groupsManager = $this->getManagerMock(array(
            'removeMember'
        ));
        $groupsManager->expects($this->once())
            ->method('removeMember')
            ->with(array(
            'group' => $groupId,
            'member' => $memberId
        ));
        $service->setGroupsManager($groupsManager);
        
        $this->assertTrue($service->removeUserFromGroup($userId, $groupId));
    }


    public function testGetMemberByUserWithGeneralException()
    {
        $this->setExpectedException('InoPerunApi\Manager\Exception\PerunErrorException', 'general error');
        
        $voId = 123;
        $userId = 456;
        
        $exception = new PerunErrorException('general error');
        
        $membersManager = $this->getManagerMock(array(
            'getMemberByUser'
        ));
        $membersManager->expects($this->once())
            ->method('getMemberByUser')
            ->with(array(
            'vo' => $voId,
            'user' => $userId
        ))
            ->will($this->throwException($exception));
        
        $this->service->setMembersManager($membersManager);
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $this->service->getMemberByUser($userId);
    }


    public function testGetMemberByUserWithNotExistsException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\MemberRetrievalException');
        
        $voId = 123;
        $userId = 456;
        
        $exception = new PerunErrorException();
        $exception->setErrorName(Service::PERUN_EXCEPTION_USER_NOT_EXISTS);
        
        $membersManager = $this->getManagerMock(array(
            'getMemberByUser'
        ));
        $membersManager->expects($this->once())
            ->method('getMemberByUser')
            ->with(array(
            'vo' => $voId,
            'user' => $userId
        ))
            ->will($this->throwException($exception));
        
        $this->service->setMembersManager($membersManager);
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $this->service->getMemberByUser($userId);
    }


    public function testGetMemberByUser()
    {
        $voId = 123;
        $userId = 456;
        $member = $this->getMemberMock();
        
        $exception = new PerunErrorException();
        $exception->setErrorName(Service::PERUN_EXCEPTION_USER_NOT_EXISTS);
        
        $membersManager = $this->getManagerMock(array(
            'getMemberByUser'
        ));
        $membersManager->expects($this->once())
            ->method('getMemberByUser')
            ->with(array(
            'vo' => $voId,
            'user' => $userId
        ))
            ->will($this->returnValue($member));
        
        $this->service->setMembersManager($membersManager);
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $this->assertSame($member, $this->service->getMemberByUser($userId));
    }
    
    /*
     * 
     */
    
    /**
     * @param unknown_type $managerName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getManagerMock($methods = array(), $managerName = null)
    {
        $manager = $this->getMockBuilder('InoPerunApi\Manager\GenericManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        
        return $manager;
    }


    protected function getGroupsCollectionMock()
    {
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        return $groups;
    }


    protected function getGroupMock()
    {
        $group = $this->getMock('InoPerunApi\Entity\Group');
        return $group;
    }


    protected function getUserCollectionMock()
    {
        $userCollection = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        return $userCollection;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityFactoryMock()
    {
        $entityFactory = $this->getMock('InoPerunApi\Entity\Factory\FactoryInterface');
        return $entityFactory;
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
}
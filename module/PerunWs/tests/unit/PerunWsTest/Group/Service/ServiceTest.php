<?php

namespace PerunWsTest\Group\Service;

use Zend\Stdlib\Parameters;
use PerunWs\Group\Service\Service;
use InoPerunApi\Manager\Exception\PerunErrorException;
use PerunWs\Group\Service\Exception\GroupCreationException;


class ServiceTest extends \PHPUnit_Framework_Testcase
{

    protected $service;


    public function setUp()
    {
        $this->service = new Service(new Parameters());
    }


    public function testGetManagerWithImplicitValue()
    {
        $groupsManagerName = 'foo';
        $membersManagerName = 'bar';
        $groupsManager = $this->createManagerMock();
        $membersManager = $this->createManagerMock();
        
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
        $baseGroupId = 456;
        $params = array(
            'vo_id' => $voId,
            'base_group_id' => $baseGroupId
        );
        $groups = $this->createGroupsCollectionMock();
        
        $this->service->setParameters(new Parameters($params));
        
        $groupsManager = $this->createManagerMock(array(
            'getSubGroups'
        ));
        $groupsManager->expects($this->once())
            ->method('getSubGroups')
            ->with(array(
            'parentGroup' => $baseGroupId
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
        
        $groups = $this->createGroupsCollectionMock();
        
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


    public function testFetchWithWrongParentGroup()
    {
        $id = 123;
        $baseGroupId = 456;
        $wrongParentGroup = 789;
        $admins = $this->createUserCollectionMock();
        
        $this->service->setParameters(new Parameters(array(
            'base_group_id' => $baseGroupId
        )));
        
        $group = $this->getMockBuilder('InoPerunApi\Entity\Group')
            ->setMethods(array(
            'getParentGroupId'
        ))
            ->getMock();
        
        $group->expects($this->once())
            ->method('getParentGroupId')
            ->will($this->returnValue($wrongParentGroup));
        
        $groupsManager = $this->createManagerMock(array(
            'getGroupById'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->returnValue($group));
        
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertNull($this->service->fetch($id));
    }


    public function testFetch()
    {
        $id = 123;
        $baseGroupId = 456;
        $admins = $this->createUserCollectionMock();
        
        $this->service->setParameters(new Parameters(array(
            'base_group_id' => $baseGroupId
        )));
        
        $group = $this->getMockBuilder('InoPerunApi\Entity\Group')
            ->setMethods(array(
            'setAdmins',
            'getParentGroupId'
        ))
            ->getMock();
        $group->expects($this->once())
            ->method('setAdmins')
            ->with($admins);
        $group->expects($this->once())
            ->method('getParentGroupId')
            ->will($this->returnValue($baseGroupId));
        
        $groupsManager = $this->createManagerMock(array(
            'getGroupById',
            'getAdmins'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->returnValue($group));
        $groupsManager->expects($this->once())
            ->method('getAdmins')
            ->with(array(
            'group' => $id
        ))
            ->will($this->returnValue($admins));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($group, $this->service->fetch($id));
    }


    public function testFetchWithGeneralException()
    {
        $this->setExpectedException('Exception', 'general exception');
        
        $id = 123;
        
        $groupsManager = $this->createManagerMock(array(
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
        
        $groupsManager = $this->createManagerMock(array(
            'getGroupById'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->fetch($id);
    }


    public function testFetchWithCustomPerunErrorException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupRetrievalException', null, 400);
        
        $id = 123;
        $exception = new PerunErrorException('perun error');
        
        $groupsManager = $this->createManagerMock(array(
            'getGroupById'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupById')
            ->with(array(
            'id' => $id
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->fetch($id);
    }


    public function testCreateWithNoName()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupCreationException', "Missing field 'name'", 400);
        
        $data = new \stdClass();
        $this->service->create($data);
    }


    public function testCreateWithException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupCreationException', null, 400);
        
        $voId = 123;
        $baseGroupId = 456;
        $data = new \stdClass();
        $exception = new PerunErrorException();
        
        $data->name = 'foo';
        $data->description = 'bar';
        
        $group = $this->createGroupMock();
        $newGroup = $this->createGroupMock();
        
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId,
            'base_group_id' => $baseGroupId
        )));
        
        $entityFactory = $this->createEntityFactoryMock();
        $entityFactory->expects($this->once())
            ->method('createEntityWithName')
            ->with('Group', array(
            'name' => $data->name,
            'description' => $data->description,
            'parentGroupId' => $baseGroupId
        ))
            ->will($this->returnValue($group));
        $this->service->setEntityFactory($entityFactory);
        
        $groupsManager = $this->createManagerMock(array(
            'createGroup'
        ));
        $groupsManager->expects($this->once())
            ->method('createGroup')
            ->with(array(
            'vo' => $voId,
            'group' => $group
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($newGroup, $this->service->create($data));
    }


    public function testCreate()
    {
        $voId = 123;
        $baseGroupId = 456;
        $data = new \stdClass();
        
        $data->name = 'foo';
        $data->description = 'bar';
        
        $group = $this->createGroupMock();
        $newGroup = $this->createGroupMock();
        
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId,
            'base_group_id' => $baseGroupId
        )));
        
        $entityFactory = $this->createEntityFactoryMock();
        $entityFactory->expects($this->once())
            ->method('createEntityWithName')
            ->with('Group', array(
            'name' => $data->name,
            'description' => $data->description,
            'parentGroupId' => $baseGroupId
        ))
            ->will($this->returnValue($group));
        $this->service->setEntityFactory($entityFactory);
        
        $groupsManager = $this->createManagerMock(array(
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
    public function testDeleteWithException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupDeleteException', null, 400);
        
        $id = 123;
        $exception = new PerunErrorException();
        
        $groupsManager = $this->createManagerMock(array(
            'deleteGroup'
        ));
        $groupsManager->expects($this->once())
            ->method('deleteGroup')
            ->with(array(
            'group' => $id
        ))
            ->will($this->throwException($exception));
        
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->delete($id);
    }


    public function testDelete()
    {
        $id = 123;
        
        $groupsManager = $this->createManagerMock(array(
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
        $users = $this->createUserCollectionMock();
        
        $groupsManager = $this->createManagerMock(array(
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
        
        $groupsManager = $this->createManagerMock(array(
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
        
        $groupsManager = $this->createManagerMock(array(
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
        
        $member = $this->createMemberMock($memberId);
        
        $groups = $this->createGroupsCollectionMock();
        
        $this->service->setParameters(new Parameters(array(
            'vo_id' => $voId
        )));
        
        $membersManager = $this->createManagerMock(array(
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
        
        $groupsManager = $this->createManagerMock(array(
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
        
        $member = $this->createMemberMock($memberId);
        
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
        
        $groupsManager = $this->createManagerMock(array(
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
        
        $member = $this->createMemberMock($memberId);
        
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
        
        $groupsManager = $this->createManagerMock(array(
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


    public function testFetchGroupAdminsWithPerunException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupGenericException', null, 400);
        
        $groupId = 123;
        $exception = new PerunErrorException();
        
        $groupsManager = $this->createManagerMock(array(
            'getAdmins'
        ));
        $groupsManager->expects($this->once())
            ->method('getAdmins')
            ->with(array(
            'group' => $groupId
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->fetchGroupAdmins($groupId);
    }


    public function testFetchGroupAdmins()
    {
        $groupId = 123;
        $admins = $this->createUserCollectionMock();
        
        $groupsManager = $this->createManagerMock(array(
            'getAdmins'
        ));
        $groupsManager->expects($this->once())
            ->method('getAdmins')
            ->with(array(
            'group' => $groupId
        ))
            ->will($this->returnValue($admins));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertSame($admins, $this->service->fetchGroupAdmins($groupId));
    }


    public function testAddGroupAdminWithPerunException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupGenericException', null, 400);
        
        $groupId = 123;
        $userId = 456;
        $exception = new PerunErrorException();
        
        $groupsManager = $this->createManagerMock(array(
            'addAdmin'
        ));
        $groupsManager->expects($this->once())
            ->method('addAdmin')
            ->with(array(
            'group' => $groupId,
            'user' => $userId
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->addGroupAdmin($groupId, $userId);
    }


    public function testAddGroupAdmin()
    {
        $groupId = 123;
        $userId = 456;
        
        $groupsManager = $this->createManagerMock(array(
            'addAdmin'
        ));
        $groupsManager->expects($this->once())
            ->method('addAdmin')
            ->with(array(
            'group' => $groupId,
            'user' => $userId
        ));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertTrue($this->service->addGroupAdmin($groupId, $userId));
    }


    public function testRemoveGroupAdminWithPerunException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupGenericException', null, 400);
        
        $groupId = 123;
        $userId = 456;
        $exception = new PerunErrorException();
        
        $groupsManager = $this->createManagerMock(array(
            'removeAdmin'
        ));
        $groupsManager->expects($this->once())
            ->method('removeAdmin')
            ->with(array(
            'group' => $groupId,
            'user' => $userId
        ))
            ->will($this->throwException($exception));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->removeGroupAdmin($groupId, $userId);
    }


    public function testRemoveGroupAdmin()
    {
        $groupId = 123;
        $userId = 456;
        
        $groupsManager = $this->createManagerMock(array(
            'removeAdmin'
        ));
        $groupsManager->expects($this->once())
            ->method('removeAdmin')
            ->with(array(
            'group' => $groupId,
            'user' => $userId
        ));
        $this->service->setGroupsManager($groupsManager);
        
        $this->assertTrue($this->service->removeGroupAdmin($groupId, $userId));
    }


    public function testGetMemberByUserWithGeneralException()
    {
        $this->setExpectedException('InoPerunApi\Manager\Exception\PerunErrorException', 'general error');
        
        $voId = 123;
        $userId = 456;
        
        $exception = new PerunErrorException('general error');
        
        $membersManager = $this->createManagerMock(array(
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
        
        $membersManager = $this->createManagerMock(array(
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
        $member = $this->createMemberMock();
        
        $exception = new PerunErrorException();
        $exception->setErrorName(Service::PERUN_EXCEPTION_USER_NOT_EXISTS);
        
        $membersManager = $this->createManagerMock(array(
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
    protected function createManagerMock($methods = array(), $managerName = null)
    {
        $manager = $this->getMockBuilder('InoPerunApi\Manager\GenericManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        
        return $manager;
    }


    protected function createGroupsCollectionMock()
    {
        $groups = $this->getMock('InoPerunApi\Entity\Collection\GroupCollection');
        return $groups;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createGroupMock()
    {
        $group = $this->getMock('InoPerunApi\Entity\Group');
        return $group;
    }


    protected function createUserCollectionMock()
    {
        $userCollection = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        return $userCollection;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityFactoryMock()
    {
        $entityFactory = $this->getMock('InoPerunApi\Entity\Factory\FactoryInterface');
        return $entityFactory;
    }


    protected function createMemberMock($id = null)
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
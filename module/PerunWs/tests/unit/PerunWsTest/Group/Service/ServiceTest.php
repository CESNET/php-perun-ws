<?php

namespace PerunWsTest\Group\Service;

use Zend\Stdlib\Parameters;
use InoPerunApi\Entity\Group;
use InoPerunApi\Entity\Collection\GroupCollection;
use InoPerunApi\Manager\Exception\PerunErrorException;
use PerunWs\Group\Service\Exception\GroupRetrievalException;
use PerunWs\Group\Service\Service;
use PerunWs\Group\Service\Exception\GroupCreationException;


class ServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $service;


    public function setUp()
    {
        $this->service = new Service($this->createTypeMapMock());
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
        $filterTypes = array(
            'foo',
            'bar'
        );
        
        $params = new Parameters(array(
            'filter_type' => $filterTypes
        ));
        
        $groups = $this->createGroupsCollectionMock();
        $filteredGroups = $this->createGroupsCollectionMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchAllGroupsByType',
            'processGroups'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchAllGroupsByType')
            ->with($filterTypes)
            ->will($this->returnValue($groups));
        
        $service->expects($this->once())
            ->method('processGroups')
            ->with($groups)
            ->will($this->returnValue($filteredGroups));
        
        $this->assertSame($filteredGroups, $service->fetchAll($params));
    }


    public function testFetchAllWithDefaultGroupTypes()
    {
        $filterTypes = array(
            'foo',
            'bar'
        );
        
        $params = new Parameters(array(
            'filter_type' => null
        ));
        
        $groups = $this->createGroupsCollectionMock();
        $filteredGroups = $this->createGroupsCollectionMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchAllGroupsByType',
            'processGroups'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchAllGroupsByType')
            ->with($filterTypes)
            ->will($this->returnValue($groups));
        
        $service->expects($this->once())
            ->method('processGroups')
            ->with($groups)
            ->will($this->returnValue($filteredGroups));
        
        $map = $this->createTypeMapMock();
        $map->expects($this->once())
            ->method('getAllTypes')
            ->will($this->returnValue($filterTypes));
        $service->setTypeToParentGroupMap($map);
        
        $this->assertSame($filteredGroups, $service->fetchAll($params));
    }


    public function testFetch()
    {
        $groupId = 123;
        $group = $this->createGroupMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'fixGroupType'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->returnValue($group));
        
        $service->expects($this->once())
            ->method('fixGroupType')
            ->with($group);
        
        $this->assertSame($group, $service->fetch($groupId));
    }


    public function testFetchWithGeneralException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupRetrievalException', 'group error', 400);
        
        $groupId = 123;
        $exception = new GroupRetrievalException('group error', 400);
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->throwException($exception));
        
        $service->fetch($groupId);
    }


    public function testFetchWithNotExistsException()
    {
        $groupId = 123;
        
        $exception = new GroupRetrievalException();
        $exception->setNotFound(true);
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->throwException($exception));
        
        $this->assertNull($service->fetch($groupId));
    }


    public function testCreateWithNoName()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupCreationException', "Missing field 'name'", 400);
        
        $data = new \stdClass();
        $this->service->create($data);
    }


    public function testCreateWithNoType()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\GroupCreationException', "Missing field 'type'", 400);
        
        $data = new \stdClass();
        $data->name = 'foo';
        $this->service->create($data);
    }


    public function testCreate()
    {
        $parentGroupId = 456;
        $data = new \stdClass();
        
        $data->name = 'foo';
        $data->description = 'bar';
        $data->type = 'sometype';
        
        $group = $this->createGroupMock();
        $newGroup = $this->createGroupMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'createGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('createGroup')
            ->with($group)
            ->will($this->returnValue($newGroup));
        
        $map = $this->createTypeMapMock();
        $map->expects($this->once())
            ->method('typeToParentGroup')
            ->with($data->type)
            ->will($this->returnValue($parentGroupId));
        $service->setTypeToParentGroupMap($map);
        
        $entityFactory = $this->createEntityFactoryMock();
        $entityFactory->expects($this->once())
            ->method('createEntityWithName')
            ->with('Group', array(
            'name' => $data->name,
            'description' => $data->description,
            'parentGroupId' => $parentGroupId
        ))
            ->will($this->returnValue($group));
        $service->setEntityFactory($entityFactory);
        
        $this->assertSame($newGroup, $service->create($data));
    }


    public function testDelete()
    {
        $id = 123;
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'deleteGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($id);
        $service->expects($this->once())
            ->method('deleteGroup')
            ->with($id)
            ->will($this->returnValue(true));
        
        $this->assertTrue($service->delete($id));
    }


    public function testFetchMembers()
    {
        $id = 123;
        $members = $this->createUserCollectionMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'fetchGroupMembers'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($id);
        $service->expects($this->once())
            ->method('fetchGroupMembers')
            ->with($id)
            ->will($this->returnValue($members));
        
        $this->assertSame($members, $service->fetchMembers($id));
    }


    public function testFetchUserGroups()
    {
        $userId = 123;
        $groupTypes = array(
            'foo',
            'bar'
        );
        $vos = array(
            111,
            222
        );
        
        $member = $this->createMemberMock();
        $groups = $this->createGroupsCollectionMock();
        $filteredGroups = $this->createGroupsCollectionMock();
        
        $params = $this->createParametersMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'extractGroupTypes',
            'fetchUserGroupsFromVos',
            'filterGroupCollectionByValidation',
            'fixGroupTypes'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('extractGroupTypes')
            ->with($params)
            ->will($this->returnValue($groupTypes));
        
        $map = $this->createTypeMapMock();
        $map->expects($this->once())
            ->method('typesToVos')
            ->with($groupTypes)
            ->will($this->returnValue($vos));
        $service->setTypeToParentGroupMap($map);
        
        $service->expects($this->once())
            ->method('fetchUserGroupsFromVos')
            ->with($userId, $vos)
            ->will($this->returnValue($groups));
        
        $service->expects($this->once())
            ->method('filterGroupCollectionByValidation')
            ->with($groups)
            ->will($this->returnValue($filteredGroups));
        
        $service->expects($this->once())
            ->method('fixGroupTypes')
            ->with($filteredGroups);
        
        $this->assertSame($filteredGroups, $service->fetchUserGroups($userId, $params));
    }


    public function testAddUserToGroup()
    {
        $userId = 123;
        $groupId = 789;
        
        $group = $this->createGroupMock();
        
        $member = $this->createMemberMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'getMemberByUser',
            'addMemberToGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->returnValue($group));
        
        $service->expects($this->once())
            ->method('getMemberByUser')
            ->with($userId)
            ->will($this->returnValue($member));
        
        $service->expects($this->once())
            ->method('addMemberToGroup')
            ->with($member, $group);
        
        $this->assertSame($member, $service->addUserToGroup($userId, $groupId));
    }


    public function testRemoveUserFromGroup()
    {
        $userId = 123;
        $groupId = 789;
        
        $group = $this->createGroupMock();
        $member = $this->createMemberMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'getMemberByUser',
            'removeMemberFromGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->returnValue($group));
        
        $service->expects($this->once())
            ->method('getMemberByUser')
            ->with($userId)
            ->will($this->returnValue($member));
        
        $service->expects($this->once())
            ->method('removeMemberFromGroup')
            ->with($member, $group);
        
        $this->assertTrue($service->removeUserFromGroup($userId, $groupId));
    }


    public function testFetchAdmins()
    {
        $groupId = 123;
        $admins = $this->createUserCollectionMock();
        $group = $this->createGroupMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'fetchGroupAdmins'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->returnValue($group));
        
        $service->expects($this->once())
            ->method('fetchGroupAdmins')
            ->with($group)
            ->will($this->returnValue($admins));
        
        $this->assertSame($admins, $service->fetchAdmins($groupId));
    }


    public function testAddAdmin()
    {
        $groupId = 123;
        $userId = 456;
        $group = $this->createGroupMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'addAdminToGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->returnValue($group));
        
        $service->expects($this->once())
            ->method('addAdminToGroup')
            ->with($userId, $group);
        
        $this->assertTrue($service->addAdmin($groupId, $userId));
    }


    public function testRemoveAdmin()
    {
        $groupId = 123;
        $userId = 456;
        $group = $this->createGroupMock();
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'fetchGroup',
            'removeAdminFromGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->once())
            ->method('fetchGroup')
            ->with($groupId)
            ->will($this->returnValue($group));
        
        $service->expects($this->once())
            ->method('removeAdminFromGroup')
            ->with($userId, $group);
        
        $this->assertTrue($service->removeAdmin($groupId, $userId));
    }


    public function testGetMemberByUserWithPerunException()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\MemberRetrievalException', null, 400);
        
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
        
        $this->service->getMemberByUser($userId, $voId);
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
        
        $this->assertSame($member, $this->service->getMemberByUser($userId, $voId));
    }


    public function testIsValidGroup()
    {
        $isValid = true;
        $parentGroupId = 123;
        
        $group = $this->getMockBuilder('InoPerunApi\Entity\Group')
            ->setMethods(array(
            'getParentGroupId'
        ))
            ->getMock();
        $group->expects($this->once())
            ->method('getParentGroupId')
            ->will($this->returnValue($parentGroupId));
        
        $map = $this->createTypeMapMock();
        $map->expects($this->once())
            ->method('isValidParentGroup')
            ->with($parentGroupId)
            ->will($this->returnValue($isValid));
        $this->service->setTypeToParentGroupMap($map);
        
        $this->assertTrue($this->service->isValidGroup($group));
    }


    public function testFilterGroupCollectionByValidation()
    {
        $parentId = 123;
        $wrongId = 456;
        
        $group1 = new Group();
        $group2 = new Group();
        $group3 = new Group();
        
        $groups = new GroupCollection();
        $groups->append($group1);
        $groups->append($group2);
        $groups->append($group3);
        
        $service = $this->getMockBuilder('PerunWs\Group\Service\Service')
            ->setMethods(array(
            'isValidGroup'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $service->expects($this->at(0))
            ->method('isValidGroup')
            ->with($group1)
            ->will($this->returnValue(true));
        $service->expects($this->at(1))
            ->method('isValidGroup')
            ->with($group2)
            ->will($this->returnValue(false));
        $service->expects($this->at(2))
            ->method('isValidGroup')
            ->with($group3)
            ->will($this->returnValue(true));
        
        $groups = $service->filterGroupCollectionByValidation($groups);
        
        $this->assertCount(2, $groups);
        $this->assertSame($group1, $groups->getAt(0));
        $this->assertSame($group3, $groups->getAt(1));
    }


    public function testFilterGroupCollectionById()
    {
        $filterIds = array(
            2,
            3,
            5
        );
        $groupIds = array(
            1,
            2,
            3,
            4,
            5,
            8,
            9
        );
        
        $groupList = array();
        foreach ($groupIds as $id) {
            $group = new Group();
            $group->setId($id);
            
            $groupList[] = $group;
        }
        
        $groups = new GroupCollection($groupList);
        $groups = $this->service->filterGroupCollectionById($groups, $filterIds);
        
        $this->assertCount(3, $groups);
        $this->assertSame($groupList[1], $groups->getAt(0));
        $this->assertSame($groupList[2], $groups->getAt(1));
        $this->assertSame($groupList[4], $groups->getAt(2));
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
        $client = $this->getMockBuilder('InoPerunApi\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('sendRequest')
            ->will($this->throwException(new \RuntimeException('Not expected to be called!')));
        
        $manager = $this->getMockBuilder('InoPerunApi\Manager\GenericManager')
            ->setConstructorArgs(array(
            $client
        ))
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


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createTypeMapMock()
    {
        $map = $this->getMockBuilder('PerunWs\Group\TypeToParentGroupMap')
            ->disableOriginalConstructor()
            ->getMock();
        
        return $map;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParametersMock()
    {
        return $this->getMock('Zend\Stdlib\Parameters');
    }
}
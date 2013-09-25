<?php

namespace PerunWsTest\Group\Service;

use Zend\Stdlib\Parameters;
use PerunWs\Group\Service\Service;


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
            ->with('Group', 
            array(
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
        
        $groupsManager = $this->getManagerMock(array(
            'getGroupRichMembers'
        ));
        $groupsManager->expects($this->once())
            ->method('getGroupRichMembers')
            ->with(array(
            'group' => $id
        ));
        $this->service->setGroupsManager($groupsManager);
        
        $this->service->fetchMembers($id);
    }


    /* FIXME
    public function testFetchUserGroupsWithInvalidMember()
    {
        $this->setExpectedException('PerunWs\Group\Service\Exception\MemberRetrievalException');
        
        $userId = 123;
        $voId = 456;
        
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
            ->will($this->throwException(new \Exception()));
        $this->service->setMembersManager($membersManager);
        
        $this->service->fetchUserGroups($userId);
    }
    */


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
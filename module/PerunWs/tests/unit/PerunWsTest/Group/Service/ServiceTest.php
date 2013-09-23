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
}
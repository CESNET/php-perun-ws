<?php

namespace PerunWsTest\Group;

use InoPerunApi\Entity\Group;
use PerunWs\Group\Hydrator;


class HydratorTest extends \PHPUnit_Framework_Testcase
{

    protected $hydrator;


    public function setUp()
    {
        $this->hydrator = new Hydrator();
    }


    public function testHydrateWithInvalidObject()
    {
        $this->setExpectedException('PerunWs\Hydrator\Exception\UnsupportedObjectException');
        
        $group = new \stdClass();
        $this->hydrator->extract($group);
    }


    public function testHydrateWithGroupEntity()
    {
        $groupId = 123;
        $groupName = 'group';
        $groupUniqueName = 'parent:group';
        $groupDescription = 'description';
        $parentId = 456;
        $admins = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        
        $expectedData = array(
            'id' => $groupId,
            'name' => $groupName,
            'unique_name' => $groupUniqueName,
            'description' => $groupDescription,
            'parent_group_id' => $parentId,
            'admins' => $admins
        );
        
        $group = new Group();
        $group->setId($groupId);
        $group->setShortName($groupName);
        $group->setName($groupUniqueName);
        $group->setDescription($groupDescription);
        $group->setParentGroupId($parentId);
        $group->setAdmins($admins);
        
        $this->assertEquals($expectedData, $this->hydrator->extract($group));
    }
}
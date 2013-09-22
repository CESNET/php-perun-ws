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
        $groupDescription = 'description';
        $parentId = 456;
        
        $expectedData = array(
            'id' => $groupId,
            'name' => $groupName,
            'description' => $groupDescription,
            'parent_group_id' => $parentId
        );
        
        $group = new Group();
        $group->setId($groupId);
        $group->setName($groupName);
        $group->setDescription($groupDescription);
        $group->setParentGroupId($parentId);
        
        $this->assertEquals($expectedData, $this->hydrator->extract($group));
    }
}
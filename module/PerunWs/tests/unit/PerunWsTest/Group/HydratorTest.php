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
        $groupType = 'foo';
        $groupDescription = 'description';
        $admins = $this->getMock('InoPerunApi\Entity\Collection\UserCollection');
        
        $expectedData = array(
            'id' => $groupId,
            'name' => $groupName,
            'unique_name' => $groupUniqueName,
            'type' => 'foo',
            'description' => $groupDescription,
            'admins' => $admins
        );
        
        $group = new Group();
        $group->setId($groupId);
        $group->setShortName($groupName);
        $group->setName($groupUniqueName);
        $group->setType($groupType);
        $group->setDescription($groupDescription);
        $group->setAdmins($admins);
        
        $this->assertEquals($expectedData, $this->hydrator->extract($group));
    }
}
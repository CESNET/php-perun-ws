<?php

namespace PerunWsTest\Group;

use PerunWs\Group\TypeToParentGroupMap;


class TypeToParentGroupMapTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructor()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123,
                'vo_id' => 456
            )
        );
        $defaultType = 'foo';
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $this->assertSame($mapDef, $map->getMapDef());
    }


    public function testTypeToParentGroup()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $this->assertSame(123, $map->typeToParentGroup('foo'));
    }


    public function testTypeToParentGroupWithUnknownType()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unknown group type');
        
        $mapDef = array(
            'foo' => array()
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $map->typeToParentGroup('bar');
    }


    public function testTypeToParentGroupWithMissingGroup()
    {
        $this->setExpectedException('RuntimeException', 'Missing group ID for type');
        
        $mapDef = array(
            'foo' => array()
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $map->typeToParentGroup('foo');
    }


    public function testTypeToVoWithUnknownType()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unknown group type');
        
        $mapDef = array(
            'foo' => array(
                'vo_id' => 123
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $map->typeToVo('bar');
    }


    public function testTypeToVoWithMissingVo()
    {
        $this->setExpectedException('RuntimeException', 'Missing VO ID for type');
        
        $mapDef = array(
            'foo' => array()
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $map->typeToVo('foo');
    }


    public function testTypeToVo()
    {
        $mapDef = array(
            'foo' => array(
                'vo_id' => 123
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $this->assertSame(123, $map->typeToVo('foo'));
    }


    public function testParentGroupToTypeWithUnknownGroup()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unknown group ID');
        
        $mapDef = array(
            'foo' => array(
                'group_id' => 123
            ),
            'bar' => array(
                'group_id' => 789
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        $map->parentGroupToType(456);
    }


    public function testParentGroupToType()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123
            ),
            'bar' => array(
                'group_id' => 789
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $this->assertSame('foo', $map->parentGroupToType(123));
    }


    public function testIsValidParentGroup()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123
            ),
            'bar' => array(
                'group_id' => 789
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $this->assertTrue($map->isValidParentGroup(123));
        $this->assertFalse($map->isValidParentGroup(456));
        $this->assertTrue($map->isValidParentGroup(789));
    }


    public function testGetDefaultType()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123
            ),
            'bar' => array(
                'group_id' => 789
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $this->assertSame('foo', $map->getDefaultType());
    }


    public function testGetAllTypes()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123
            ),
            'bar' => array(
                'group_id' => 789
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $expected = array(
            'foo',
            'bar'
        );
        
        $this->assertSame($expected, $map->getAllTypes());
    }


    public function testTypesToVos()
    {
        $mapDef = array(
            'foo' => array(
                'group_id' => 123,
                'vo_id' => 111
            ),
            'bar' => array(
                'group_id' => 789,
                'vo_id' => 222
            ),
            'test' => array(
                'group_id' => 789,
                'vo_id' => 222
            )
        );
        
        $map = new TypeToParentGroupMap($mapDef);
        
        $expected = array(
            111,
            222
        );
        
        $this->assertSame($expected, $map->typesToVos(array(
            'foo',
            'bar',
            'test'
        )));
    }
}
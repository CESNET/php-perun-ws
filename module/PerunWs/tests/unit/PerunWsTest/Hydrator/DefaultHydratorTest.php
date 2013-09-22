<?php

namespace PerunWsTest\Hydrator;

use PerunWs\Hydrator\DefaultHydrator;


class DefaultHydratorTest extends \PHPUnit_Framework_TestCase
{

    protected $hydrator;


    public function setUp()
    {
        $this->hydrator = new DefaultHydrator();
    }


    public function testExtractWithInvalidObject()
    {
        $this->setExpectedException('PerunWs\Hydrator\Exception\UnsupportedObjectException');
        
        $object = new \stdClass();
        $this->hydrator->extract($object);
    }


    public function testExtractWithGenericEntity()
    {
        $props = array(
            'foo' => 'bar'
        );
        
        $object = $this->getMock('InoPerunApi\Entity\GenericEntity');
        $object->expects($this->once())
            ->method('getProperties')
            ->will($this->returnValue($props));
        
        $this->assertSame($props, $this->hydrator->extract($object));
    }
}
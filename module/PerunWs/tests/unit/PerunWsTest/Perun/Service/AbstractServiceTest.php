<?php

namespace PerunWsTest\Perun\Service;


class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{


    public function testGetEntityManagerFactoryWithMissingException()
    {
        $this->setExpectedException('PerunWs\Exception\MissingDependencyException');
        
        $service = $this->getMockForAbstractClass('PerunWs\Perun\Service\AbstractService');
        $service->getEntityManagerFactory();
    }


    public function testSetEntityManagerFactory()
    {
        $service = $this->getMockForAbstractClass('PerunWs\Perun\Service\AbstractService');
        $factory = $this->getMock('InoPerunApi\Manager\Factory\FactoryInterface');
        
        $service->setEntityManagerFactory($factory);
        $this->assertSame($factory, $service->getEntityManagerFactory());
    }
}
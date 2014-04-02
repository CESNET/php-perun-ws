<?php

namespace PerunWsTest\Perun\Service;


use Zend\Stdlib\Parameters;
class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{


    public function testGetEntityManagerFactoryWithMissingException()
    {
        $this->setExpectedException('PerunWs\Exception\MissingDependencyException');
        
        $service = $this->getServiceMock();
        $service->getEntityManagerFactory();
    }


    public function testSetEntityManagerFactory()
    {
        $service = $this->getServiceMock();
        $factory = $this->getMock('InoPerunApi\Manager\Factory\FactoryInterface');
        
        $service->setEntityManagerFactory($factory);
        $this->assertSame($factory, $service->getEntityManagerFactory());
    }


    public function testCreateManager()
    {
        $managerName = 'fooManager';
        
        $service = $this->getServiceMock();
        
        $manager = $this->getMock('InoPerunApi\Manager\Factory\GenericManager');
        
        $factory = $this->getMock('InoPerunApi\Manager\Factory\FactoryInterface');
        $factory->expects($this->once())
            ->method('createManager')
            ->with($managerName)
            ->will($this->returnValue($manager));
        
        $service->setEntityManagerFactory($factory);
        
        $this->assertSame($manager, $service->createManager($managerName));
    }


    public function testGetVoIdWithMissingException()
    {
        $this->setExpectedException('PerunWs\Perun\Service\Exception\MissingParameterException');
        
        $service = $this->getServiceMock();
        $service->getVoId();
    }


    public function testGetVoId()
    {
        $voId = 456;
        
        $service = $this->getServiceMock(array(
            'vo_id' => $voId
        ));
        $this->assertSame($voId, $service->getVoId());
    }


    public function testGetPrincipalNamesAttributeNameWithMissingException()
    {
        $this->setExpectedException('PerunWs\Perun\Service\Exception\MissingParameterException');
        
        $service = $this->getServiceMock();
        $service->getPrincipalNamesAttributeName();
    }


    public function testGetPrincipalNamesAttributeName()
    {
        $attrName = 'ePPN';
        
        $service = $this->getServiceMock(array(
            'principal_names_attribute_name' => $attrName
        ));
        $this->assertSame($attrName, $service->getPrincipalNamesAttributeName());
    }


    public function testGetBaseGroupIdWithMissingException()
    {
        $this->setExpectedException('PerunWs\Perun\Service\Exception\MissingParameterException');
        
        $service = $this->getServiceMock();
        $service->getBaseGroupId();
    }


    public function testGetBaseGroupId()
    {
        $groupId = 123;
        
        $service = $this->getServiceMock(array(
            'base_group_id' => $groupId
        ));
        
        $this->assertSame($groupId, $service->getBaseGroupId());
    }
    
    /*
     * 
     */
    
    /**
     * @param array $params
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getServiceMock($params = array())
    {
        //$parameters = $this->getParametersMock($params);
        $parameters = new Parameters($params);
        $service = $this->getMockBuilder('PerunWs\Perun\Service\AbstractService')
            ->setConstructorArgs(array(
            $parameters
        ))
            ->getMockForAbstractClass();
        
        return $service;
    }


    /*
    protected function getParametersMock(array $params = array())
    {
        $parameters = $this->getMock('Zend\Stdlib\Parameters');
        foreach ($params as $key => $value) {
            $parameters->expects($this->any())
                ->method('get')
                ->with($key)
                ->will($this->returnValue($value));
        }
        
        return $parameters;
    }
    */
}
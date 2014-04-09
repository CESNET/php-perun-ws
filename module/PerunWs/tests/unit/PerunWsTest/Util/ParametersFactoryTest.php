<?php

namespace PerunWsTest\Util;

use PerunWs\Util\ParametersFactory;


class ParametersFactoryTest extends \PHPUnit_Framework_TestCase
{


    public function testCreateParameters()
    {
        $params = array(
            'foo' => 'bar'
        );
        
        $factory = new ParametersFactory();
        $parameters = $factory->createParameters($params);
        
        $this->assertInstanceOf('Zend\Stdlib\Parameters', $parameters);
        $this->assertSame($params, $parameters->getArrayCopy());
    }
}
<?php

namespace PerunWsTest\User;

use PerunWs\User\Listener;


class ListenerTest extends \PHPUnit_Framework_TestCase
{

    protected $listener;


    public function setUp()
    {
        $service = $this->getServiceMock();
        $this->listener = new Listener($service);
    }


    public function test()
    {
        $this->assertTrue(true);
    }
    
    /*
     * 
     */
    protected function getServiceMock()
    {
        $service = $this->getMockBuilder('PerunWs\User\Service\Service')
            ->disableOriginalConstructor()
            ->getMock();
        return $service;
    }
}
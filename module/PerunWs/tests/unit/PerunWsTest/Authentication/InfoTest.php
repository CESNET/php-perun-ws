<?php

namespace PerunWsTest\Authentication;

use PerunWs\Authentication\Info;


class InfoTest extends \PHPUnit_Framework_TestCase
{


    public function testSetClientName()
    {
        $info = new Info('foo');
        $this->assertSame('foo', $info->getClientId());
        $info->setClientId('bar');
        $this->assertSame('bar', $info->getClientId());
    }


    public function testSetClientDescription()
    {
        $info = new Info('foo');
        $info->setClientDescription('some desc');
        $this->assertSame('some desc', $info->getClientDescription());
    }


    public function testConstructor()
    {
        $name = 'foo';
        $desc = 'some desc';
        
        $info = new Info($name, $desc);
        $this->assertSame($name, $info->getClientId());
        $this->assertSame($desc, $info->getClientDescription());
    }
}
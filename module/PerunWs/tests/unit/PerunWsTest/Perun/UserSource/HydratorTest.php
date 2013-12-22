<?php

namespace PerunWsTest\Perun\UserSource;

use InoPerunApi\Entity\ExtSource;
use InoPerunApi\Entity\UserExtSource;
use PerunWs\Perun\UserSource\Hydrator;


class HydratorTest extends \PHPUnit_Framework_Testcase
{

    protected $hydrator;


    public function setUp()
    {
        $this->hydrator = new Hydrator();
    }


    public function testExtractWithUnsupportedObject()
    {
        $this->setExpectedException('PerunWs\Hydrator\Exception\UnsupportedObjectException');
        
        $this->hydrator->extract(new \stdClass());
    }


    public function testExtract()
    {
        $id = 123;
        $loa = 2;
        $name = 'user_ext_source';
        $login = 'foo';
        
        $userSource = new UserExtSource(array(
            'id' => $id,
            'loa' => $loa,
            'login' => $login,
            'extSource' => new ExtSource(array(
                'name' => $name
            ))
        ));
        
        $data = $this->hydrator->extract($userSource);
        
        $this->assertSame($id, $data['id']);
        $this->assertSame($loa, $data['loa']);
        $this->assertSame($login, $data['login']);
        $this->assertSame($name, $data['name']);
    }
}
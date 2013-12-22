<?php

namespace PerunWsTest\Member;

use InoPerunApi\Entity\Member;
use PerunWs\Member\Hydrator;


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
        $userId = 456;
        $memberId = 123;
        $memberStatus = 'unknown';
        $userSourceId = 789;
        
        $userData = array(
            'id' => $userId
        );
        $userSourceData = array(
            'id' => $userSourceId
        );
        
        $userAttributes = $this->getMock('InoPerunApi\Entity\Collection\AttributeCollection');
        
        $user = $this->getMockBuilder('InoPerunApi\User\User')
            ->setMethods(array(
            'setUserAttributes'
        ))
            ->getMock();
        $user->expects($this->once())
            ->method('setUserAttributes')
            ->with($userAttributes);
        
        $userHydrator = $this->getMock('PerunWs\User\Hydrator');
        $userHydrator->expects($this->once())
            ->method('extract')
            ->with($user)
            ->will($this->returnValue($userData));
        $this->hydrator->setUserHydrator($userHydrator);
        
        $userExtSource = $this->getMock('InoPerunApi\Entity\UserExtSource');
        $userSourceHydrator = $this->getMock('PerunWs\Perun\UserSource\Hydrator');
        $userSourceHydrator->expects($this->at(0))
            ->method('extract')
            ->with($userExtSource)
            ->will($this->returnValue($userSourceData));
        $this->hydrator->setUserSourceHydrator($userSourceHydrator);
        
        $member = new Member(array(
            'id' => $memberId,
            'status' => $memberStatus,
            'user' => $user,
            'userAttributes' => $userAttributes,
            'userExtSources' => array(
                $userExtSource
            )
        ));
        
        $data = $this->hydrator->extract($member);
        
        $this->assertSame($userId, $data['id']);
        $this->assertSame($memberId, $data['member_id']);
        $this->assertSame($memberStatus, $data['member_status']);
        $this->assertSame($userSourceId, $data['sources'][0]['id']);
    }
}
<?php

namespace PerunWsTest\User;

use PerunWs\User\Hydrator;
use InoPerunApi\Entity\User;
use InoPerunApi\Entity\Collection\Collection;
use InoPerunApi\Entity\Attribute;


class HydratorTest extends \PHPUnit_Framework_TestCase
{

    protected $hydrator;


    public function setUp()
    {
        $this->hydrator = new Hydrator();
    }


    public function testExtractWithInvalidObject()
    {
        $this->setExpectedException('PerunWs\Hydrator\Exception\UnsupportedObjectException');
        
        $this->hydrator->extract(new \stdClass());
    }


    public function testExtract()
    {
        $id = 123;
        $firstName = 'Ivan';
        $lastName = 'Novakov';
        $displayName = 'Ivan Novakov';
        $organization = 'Foo Inc.';
        $mail = 'novakov@foo.org';
        $phone = '123456';
        $preferredLanguage = 'pt';
        $principalNames = array(
            'foo',
            'bar'
        );
        $timezone = 'Some/Zone';
        
        $user = new User(array(
            'id' => $id,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'userAttributes' => new Collection(array(
                new Attribute(array(
                    'friendlyName' => 'organization',
                    'value' => $organization
                )),
                new Attribute(array(
                    'friendlyName' => 'preferredMail',
                    'value' => $mail
                )),
                new Attribute(array(
                    'friendlyName' => 'displayName',
                    'value' => $displayName
                )),
                new Attribute(array(
                    'friendlyName' => 'phone',
                    'value' => $phone
                )),
                new Attribute(array(
                    'friendlyName' => 'preferredLanguage',
                    'value' => $preferredLanguage
                )),
                new Attribute(array(
                    'friendlyName' => 'eduPersonPrincipalNames',
                    'value' => $principalNames
                )),
                new Attribute(array(
                    'friendlyName' => 'timezone',
                    'value' => $timezone
                ))
            ))
        ));
        
        $data = $this->hydrator->extract($user);
        $this->assertSame($id, $data['id']);
        $this->assertSame($firstName, $data['first_name']);
        $this->assertSame($lastName, $data['last_name']);
        $this->assertSame($displayName, $data['display_name']);
        $this->assertSame($mail, $data['mail']);
        $this->assertSame($organization, $data['organization']);
        $this->assertSame($phone, $data['phone']);
        $this->assertSame($preferredLanguage, $data['language']);
        $this->assertSame($principalNames, $data['principal_names']);
        $this->assertSame($timezone, $data['timezone']);
    }
}
<?php

namespace PerunWs\Member;

use Zend\Stdlib\Hydrator\HydratorInterface;
use PerunWs\User;
use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use InoPerunApi\Entity\Member;


class Hydrator implements HydratorInterface
{

    const FIELD_MEMBER_ID = 'member_id';

    const FIELD_MEMBER_STATUS = 'member_status';

    protected $userHydrator;


    public function getUserHydrator()
    {
        if (! $this->userHydrator instanceof User\Hydrator) {
            $this->userHydrator = new User\Hydrator();
        }
        return $this->userHydrator;
    }


    public function hydrate(array $data, $member)
    {}


    public function extract($member)
    {
        //_dump($member->getRichUser());
        if (! $member instanceof Member) {
            throw new UnsupportedObjectException(get_class($member));
        }
        
        $user = $member->getUser();
        $userAttributes = $member->getUserAttributes();

        $user->setUserAttributes($userAttributes);
        $data = $this->getUserHydrator()->extract($user);
        
        $data += array(
            self::FIELD_MEMBER_ID => $member->getId(),
            self::FIELD_MEMBER_STATUS => $member->getStatus()
        );
        
        return $data;
    }
}
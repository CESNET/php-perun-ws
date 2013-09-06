<?php

namespace PerunWs\Member;

use Zend\Stdlib\Hydrator\HydratorInterface;
use PerunWs\User;
use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use InoPerunApi\Entity\Member;


/**
 * Member hydrator.
 */
class Hydrator implements HydratorInterface
{

    const FIELD_MEMBER_ID = 'member_id';

    const FIELD_MEMBER_STATUS = 'member_status';

    protected $userHydrator;


    /**
     * @return \PerunWs\User\Hydrator
     */
    public function getUserHydrator()
    {
        if (! $this->userHydrator instanceof User\Hydrator) {
            $this->userHydrator = new User\Hydrator();
        }
        return $this->userHydrator;
    }


    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $member)
    {}


    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($member)
    {
        // _dump($member->getRichUser());
        if (! $member instanceof Member) {
            throw new UnsupportedObjectException(get_class($member));
        }
        
        $user = $member->getUser();
        $data = array();
        
        if ($user) {
            $userAttributes = $member->getUserAttributes();
            
            $user->setUserAttributes($userAttributes);
            $data = $this->getUserHydrator()->extract($user);
        }
        
        $data += array(
            self::FIELD_MEMBER_ID => $member->getId(),
            self::FIELD_MEMBER_STATUS => $member->getStatus()
        );
        
        return $data;
    }
}
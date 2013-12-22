<?php

namespace PerunWs\Member;

use Zend\Stdlib\Hydrator\HydratorInterface;
use InoPerunApi\Entity\Member;
use PerunWs\User;
use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use PerunWs\Perun\UserSource;


/**
 * Member hydrator.
 */
class Hydrator implements HydratorInterface
{

    const FIELD_MEMBER_ID = 'member_id';

    const FIELD_MEMBER_STATUS = 'member_status';

    /**
     * @var User\Hydrator
     */
    protected $userHydrator;

    /**
     * @var UserSource\Hydrator
     */
    protected $userSourceHydrator;


    /**
     * @param UserSource\Hydrator $userSourceHydrator
     */
    public function setUserSourceHydrator(UserSource\Hydrator $userSourceHydrator)
    {
        $this->userSourceHydrator = $userSourceHydrator;
    }


    /**
     * @return UserSource\Hydrator
     */
    public function getUserSourceHydrator()
    {
        if (! $this->userSourceHydrator instanceof UserSource\Hydrator) {
            $this->userSourceHydrator = new UserSource\Hydrator();
        }
        return $this->userSourceHydrator;
    }


    /**
     * @param \PerunWs\User\Hydrator $userHydrator
     */
    public function setUserHydrator($userHydrator)
    {
        $this->userHydrator = $userHydrator;
    }


    /**
     * @return User\Hydrator
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
        /* @var $member \InoPerunApi\Entity\RichMember */
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
        
        $userExtSources = $member->getUserExtSources();
        if ($userExtSources) {
            foreach ($userExtSources as $userExtSource) {
                $data['sources'][] = $this->getUserSourceHydrator()->extract($userExtSource);
            }
        }
        
        $data += array(
            self::FIELD_MEMBER_ID => $member->getId(),
            self::FIELD_MEMBER_STATUS => $member->getStatus()
        );
        
        return $data;
    }
}
<?php

namespace PerunWs\User\Service;

use PerunWs\Perun\Service\AbstractService;
use InoPerunApi\Manager\GenericManager;
use InoPerunApi\Manager\Exception\PerunErrorException;
use InoPerunApi\Entity\Collection\RichMemberCollection;


/**
 * Implementation of the user service interface.
 */
class Service extends AbstractService implements ServiceInterface
{

    const PERUN_EXCEPTION_USER_NOT_EXISTS = 'UserNotExistsException';

    const PERUN_EXCEPTION_MEMBER_NOT_EXISTS = 'MemberNotExistsException';

    protected $usersManagerName = 'usersManager';

    protected $membersManagerName = 'membersManager';

    /**
     * @var array
     */
    protected $userAttributeNames = array(
        'urn:perun:user:attribute-def:def:organization',
        'urn:perun:user:attribute-def:def:preferredMail',
        'urn:perun:user:attribute-def:core:displayName',
        'urn:perun:user:attribute-def:def:phone',
        'urn:perun:user:attribute-def:def:preferredLanguage',
        'urn:perun:user:attribute-def:virt:eduPersonPrincipalNames',
        'urn:perun:user:attribute-def:def:timezone',

        /*
        'urn:perun:user:attribute-def:core:id',
        'urn:perun:user:attribute-def:core:firstName',
        'urn:perun:user:attribute-def:core:lastName'
        */
    );

    /**
     * @var GenericManager
     */
    protected $usersManager;

    /**
     * @var GenericManager
     */
    protected $membersManager;


    /**
     * @return string
     */
    public function getUsersManagerName()
    {
        return $this->usersManagerName;
    }


    /**
     * @param string $usersManagerName
     */
    public function setUsersManagerName($usersManagerName)
    {
        $this->usersManagerName = $usersManagerName;
    }


    /**
     * @return string
     */
    public function getMembersManagerName()
    {
        return $this->membersManagerName;
    }


    /**
     * @param string $membersManagerName
     */
    public function setMembersManagerName($membersManagerName)
    {
        $this->membersManagerName = $membersManagerName;
    }


    /**
     * Returns the current users entity manager.
     * 
     * @return GenericManager
     */
    public function getUsersManager()
    {
        if (! $this->usersManager instanceof GenericManager) {
            $this->usersManager = $this->createManager($this->usersManagerName);
        }
        return $this->usersManager;
    }


    /**
     * Sets explicitly the users entity manager.
     * 
     * @param GenericManager $usersManager
     */
    public function setUsersManager(GenericManager $usersManager)
    {
        $this->usersManager = $usersManager;
    }


    /**
     * Returns the current members manager.
     * 
     * @return GenericManager
     */
    public function getMembersManager()
    {
        if (! $this->membersManager instanceof GenericManager) {
            $this->membersManager = $this->createManager($this->membersManagerName);
        }
        return $this->membersManager;
    }


    /**
     * Sets explicitly the members entity manager.
     * 
     * @param GenericManager $membersManager
     */
    public function setMembersManager(GenericManager $membersManager)
    {
        $this->membersManager = $membersManager;
    }


    /**
     * @return array
     */
    public function getUserAttributeNames()
    {
        return $this->userAttributeNames;
    }


    /**
     * @param array  $userAttributeNames
     */
    public function setUserAttributeNames(array $userAttributeNames)
    {
        $this->userAttributeNames = $userAttributeNames;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        try {
            $member = $this->getMembersManager()->getMemberByUser(array(
                'vo' => $this->getVoId(),
                'user' => $id
            ));
            
            $richMember = $this->getMembersManager()->getRichMemberWithAttributes(array(
                'id' => $member->getId()
            ));
        } catch (PerunErrorException $e) {
            if (self::PERUN_EXCEPTION_MEMBER_NOT_EXISTS == $e->getErrorName()) {
                return null;
            }
            throw $e;
        }
        
        return $richMember;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll(array $params = array())
    {
        $params += array(
            'vo' => $this->getVoId(),
            'attrsNames' => $this->getUserAttributeNames()
        );
        
        if (isset($params['filter_user_id']) && is_array($params['filter_user_id'])) {
            return $this->fetchByMultipleId($params['filter_user_id']);
        }
        
        if (isset($params['searchString'])) {
            return $this->getMembersManager()->findCompleteRichMembers($params);
            // return $this->getMembersManager()->findRichMembersWithAttributesInVo($params);
        }
        
        return $this->getMembersManager()->getCompleteRichMembers($params);
        // return $this->getMembersManager()->getRichMembersWithAttributes($params);
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetchByPrincipalName()
     */
    public function fetchByPrincipalName($principalName)
    {
        $params = array(
            'attributeName' => $this->getPrincipalNamesAttributeName(),
            'attributeValue' => $principalName
        );
        
        /* @var $users \InoPerunApi\Entity\Collection\UserCollection */
        $users = $this->getUsersManager()->getUsersByAttributeValue($params);
        
        if (! $users || ! $users->count()) {
            return null;
        }
        
        if (1 < $users->count()) {
            throw new Exception\MultipleUsersPerPrincipalNameException(sprintf("Found multiple (%d) users with principal name '%s'", $users->count(), $principalName));
        }
        
        return $users->getAt(0);
    }


    /**
     * Fetches specific members by their user IDs.
     * 
     * @param array $userIdList
     * @return RichMemberCollection
     */
    public function fetchByMultipleId(array $userIdList)
    {
        $richMembers = new RichMemberCollection();
        
        foreach ($userIdList as $userId) {
            $richMember = $this->fetch($userId);
            if (null !== $richMember) {
                $richMembers->append($richMember);
            }
        }
        
        return $richMembers;
    }
}
<?php

namespace PerunWs\User\Service;

use PerunWs\Perun\Service\AbstractService;
use InoPerunApi\Manager\GenericManager;


/**
 * Implementation of the user service interface.
 */
class Service extends AbstractService implements ServiceInterface
{

    protected $usersManagerName = 'usersManager';

    protected $membersManagerName = 'membersManager';

    /**
     * @var GenericManager
     */
    protected $usersManager;

    /**
     * @var GenericManager
     */
    protected $membersManager;


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
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        try {
            $user = $this->getUsersManager()->getRichUserWithAttributes(
                array(
                    'user' => $id
                ));
        } catch (PerunErrorException $e) {
            if (self::PERUN_EXCEPTION_USER_NOT_EXISTS == $e->getErrorName()) {
                return null;
            }
            throw $e;
        }
        
        return $user;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll(array $params = array())
    {
        $params['vo'] = $this->getVoId();
        if (isset($params['principal'])) {
            $users = $this->getUsersManager()->getUsersByAttributeValue(
                array(
                    'attributeName' => $this->getPrincipalNamesAttributeName(),
                    'attributeValue' => $params['principal']
                ));
        } elseif (isset($params['searchString'])) {
            $users = $this->getMembersManager()->findRichMembersWithAttributesInVo($params);
        } else {
            $users = $this->getMembersManager()->getRichMembersWithAttributes($params);
        }
        
        return $users;
    }
}
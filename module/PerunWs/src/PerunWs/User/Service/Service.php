<?php

namespace PerunWs\User\Service;

use PerunWs\Perun\Service\AbstractService;
use InoPerunApi\Manager\GenericManager;


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
    
    // ?
    // protected $groupsManager;
    
    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        $user = $this->getUsersManager()->getRichUserWithAttributes(array(
            'user' => $id
        ));
        
        return $user;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll(array $params = array())
    {
        if (isset($params['searchString'])) {
            $users = $this->getMembersManager()->findRichMembersWithAttributesInVo($params);
        } else {
            $users = $this->getMembersManager()->getRichMembersWithAttributes($params);
        }
        
        return $users;
    }
}
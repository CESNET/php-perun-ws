<?php

namespace PerunWs\Group\Service;

use PerunWs\Perun\Service\AbstractService;
use InoPerunApi\Manager\GenericManager;
use InoPerunApi\Entity\Group;


class Service extends AbstractService implements ServiceInterface
{

    protected $groupsManagerName = 'groupsManager';

    protected $membersManagerName = 'membersManager';

    /**
     * @var GenericManager
     */
    protected $groupsManager;

    /**
     * @var GenericManager
     */
    protected $membersManager;


    /**
     * @return GenericManager
     */
    public function getGroupsManager()
    {
        if (! $this->groupsManager instanceof GenericManager) {
            $this->groupsManager = $this->createManager($this->groupsManagerName);
        }
        return $this->groupsManager;
    }


    /**
     * @param GenericManager $groupsManager
     */
    public function setGroupsManager(GenericManager $groupsManager)
    {
        $this->groupsManager = $groupsManager;
    }


    /**
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
     * @param GenericManager $membersManager
     */
    public function setMembersManager(GenericManager $membersManager)
    {
        $this->membersManager = $membersManager;
    }


    public function fetchAll()
    {
        $params = array(
            'vo' => 421
        );
        $groups = $this->getGroupsManager()->getGroups($params);
        
        return $groups;
    }


    public function fetch($id)
    {
        $group = $this->getGroupsManager()->getGroupById(array(
            'id' => $id
        ));
        
        return $group;
    }


    public function create($data)
    {
        $group = new Group(array(
            Group::PROP_NAME => $data->name
        ));
        
        $this->getGroupsManager()->createGroup(array(
            'vo' => 421,
            'group' => $group
        ));
        
        return $group;
    }


    public function patch($id, $data)
    {}


    public function delete($id)
    {}
}
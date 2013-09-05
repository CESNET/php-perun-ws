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
            Group::PROP_NAME => $data->name,
            'description' => $data->description
        ));
        
        $newGroup = $this->getGroupsManager()->createGroup(array(
            'vo' => 421,
            'group' => $group
        ));
        _dump($newGroup);
        
        return $newGroup;
    }


    public function patch($id, $data)
    {
        $data['id'] = $id;
        $group = $this->getGroupsManager()->updateGroup(array(
            'group' => $data
        ));
        
        return $group;
    }


    public function delete($id)
    {
        $this->getGroupsManager()->deleteGroup(array(
            'group' => $id
        ));
        
        return true;
    }


    public function fetchMembers($id)
    {
        $members = $this->getGroupsManager()->getGroupRichMembers(array(
            'group' => $id
        ));
        
        $_members = new \InoPerunApi\Entity\Collection\RichUserCollection(array(
            new \InoPerunApi\Entity\RichUser(array(
                'id' => 123
            ))
        ));
        
        return $members;
    }


    public function addMember($groupId, $userId)
    {}


    public function removeMember($groupId, $userId)
    {}
}
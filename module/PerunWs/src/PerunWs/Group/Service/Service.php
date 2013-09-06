<?php

namespace PerunWs\Group\Service;

use PerunWs\Perun\Service\AbstractService;
use InoPerunApi\Manager\GenericManager;
use InoPerunApi\Entity\Group;
use PerunWs\Perun\Service\Exception\MissingParameterException;


/**
 * Implementation of the group service interface.
 */
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


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll()
    {
        $params = array(
            'vo' => $this->getVoId()
        );
        $groups = $this->getGroupsManager()->getGroups($params);
        
        return $groups;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        $group = $this->getGroupsManager()->getGroupById(array(
            'id' => $id
        ));
        
        return $group;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::create()
     */
    public function create($data)
    {
        $group = new Group(array(
            Group::PROP_NAME => $data->name,
            'description' => $data->description
        ));
        
        $newGroup = $this->getGroupsManager()->createGroup(array(
            'vo' => $this->getVoId(),
            'group' => $group
        ));
        // _dump($newGroup);
        
        return $newGroup;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::patch()
     */
    public function patch($id, $data)
    {
        $data['id'] = $id;
        $group = $this->getGroupsManager()->updateGroup(array(
            'group' => $data
        ));
        
        return $group;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::delete()
     */
    public function delete($id)
    {
        $this->getGroupsManager()->deleteGroup(array(
            'group' => $id
        ));
        
        return true;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchMembers()
     */
    public function fetchMembers($id)
    {
        $members = $this->getGroupsManager()->getGroupRichMembers(array(
            'group' => $id
        ));
        
        return $members;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchUserGroups()
     */
    public function fetchUserGroups($userId)
    {
        $member = $this->getMemberByUser($userId);
        $groups = $this->getGroupsManager()->getAllMemberGroups(array(
            'member' => $member->getId()
        ));
        
        return $groups;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::addUserToGroup()
     */
    public function addUserToGroup($userId, $groupId)
    {
        $member = $this->getMemberByUser($userId);
        $this->getGroupsManager()->addMember(array(
            'group' => $groupId,
            'member' => $member->getId()
        ));
        
        return $member;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::removeUserFromGroup()
     */
    public function removeUserFromGroup($userId, $groupId)
    {
        $member = $this->getMemberByUser($userId);
        $this->getGroupsManager()->removeMember(array(
            'group' => $groupId,
            'member' => $member->getId()
        ));
        
        return true;
    }


    /**
     * Retrieves the user's corresponding "member" entity.
     * 
     * @param integer $userId
     * @return \InoPerunApi\Entity\Member|null
     */
    public function getMemberByUser($userId)
    {
        $member = null;
        
        try {
            $member = $this->getMembersManager()->getMemberByUser(array(
                'vo' => $this->getVoId(),
                'user' => $userId
            ));
        } catch (\Exception $e) {}
        
        if (null === $member) {
            throw new Exception\MemberRetrievalException(sprintf("Cannot retrieve member for user ID:%d and VO ID:%d", $userId, $this->getVoId()));
        }
        
        return $member;
    }
}
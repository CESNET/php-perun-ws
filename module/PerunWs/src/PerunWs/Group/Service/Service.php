<?php

namespace PerunWs\Group\Service;

use InoPerunApi\Entity\Member;
use Zend\Stdlib\Parameters;
use InoPerunApi\Manager\GenericManager;
use InoPerunApi\Entity;
use InoPerunApi\Manager\Exception\PerunErrorException;
use InoPerunApi\Entity\Collection\GroupCollection;
use InoPerunApi\Entity\Group;
use PerunWs\Group\TypeToParentGroupMap;
use PerunWs\Perun\Service\AbstractService;


/**
 * Implementation of the group service interface.
 */
class Service extends AbstractService implements ServiceInterface
{

    const PERUN_EXCEPTION_GROUP_NOT_EXISTS = 'GroupNotExistsException';

    const PERUN_EXCEPTION_USER_NOT_EXISTS = 'UserNotExistsException';

    const PERUN_EXCEPTION_USER_ALREADY_ADMIN = 'AlreadyAdminException';

    const PERUN_EXCEPTION_USER_NOT_ADMIN = 'UserNotAdminException';

    /**
     * The name of the group manager (remote APi object).
     * 
     * @see http://perun.metacentrum.cz/javadoc/cz/metacentrum/perun/core/api/GroupsManager.html
     * @var string
     */
    protected $groupsManagerName = 'groupsManager';

    /**
     * The name of the members manager (remote API object).
     * 
     * @see http://perun.metacentrum.cz/javadoc/cz/metacentrum/perun/core/api/MembersManager.html
     * @var string
     */
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
     * @var Entity\Factory\FactoryInterface
     */
    protected $entityFactory;

    /**
     * @var TypeToParentGroupMap
     */
    protected $typeToParentGroupMap;


    public function __construct(TypeToParentGroupMap $typeToParentGroupMap)
    {
        $this->setTypeToParentGroupMap($typeToParentGroupMap);
    }


    /**
     * @return string
     */
    public function getGroupsManagerName()
    {
        return $this->groupsManagerName;
    }


    /**
     * @param string $groupsManagerName
     */
    public function setGroupsManagerName($groupsManagerName)
    {
        $this->groupsManagerName = $groupsManagerName;
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
     * @return Entity\Factory\FactoryInterface
     */
    public function getEntityFactory()
    {
        if (! $this->entityFactory instanceof Entity\Factory\FactoryInterface) {
            $this->entityFactory = new Entity\Factory\GenericFactory();
        }
        return $this->entityFactory;
    }


    /**
     * @param Entity\Factory\FactoryInterface $entityFactory
     */
    public function setEntityFactory(Entity\Factory\FactoryInterface $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }


    /**
     * @return \PerunWs\Group\TypeToParentGroupMap
     */
    public function getTypeToParentGroupMap()
    {
        return $this->typeToParentGroupMap;
    }


    /**
     * @param \PerunWs\Group\TypeToParentGroupMap $typeToParentGroupMap
     */
    public function setTypeToParentGroupMap($typeToParentGroupMap)
    {
        $this->typeToParentGroupMap = $typeToParentGroupMap;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll(Parameters $params)
    {
        $groupTypes = $this->extractGroupTypes($params);
        
        $groups = $this->fetchAllGroupsByType($groupTypes);
        $groups = $this->processGroups($groups, $params);
        
        return $groups;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        try {
            $group = $this->fetchGroup($id);
        } catch (Exception\GroupRetrievalException $e) {
            if ($e->isNotFound()) {
                return null;
            }
            
            throw $e;
        }
        
        $this->fixGroupType($group);
        
        return $group;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::create()
     */
    public function create($data)
    {
        if (! property_exists($data, 'name')) {
            throw new Exception\GroupCreationException("Missing field 'name'", 400);
        }
        
        if (! property_exists($data, 'type')) {
            throw new Exception\GroupCreationException("Missing field 'type'", 400);
        }
        
        $parentGroupId = $this->getParentGroupIdByGroupType($data->type);
        
        $group = $this->getEntityFactory()->createEntityWithName('Group', array(
            'name' => $data->name,
            'description' => property_exists($data, 'description') ? $data->description : '',
            'parentGroupId' => $parentGroupId
        ));
        
        return $this->createGroup($group, $data->type);
    }


    /**
     * FIXME
     * Currently it doesn't work due to privilege exception:
     * Error 14319175fa6: Principal /C=CZ/O=CESNET/CN=hroch.cesnet.cz is not authorized to perform action 'updateGroup'
     * 
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::patch()
     */
    public function patch($id, $data)
    {
        $group = $this->fetchGroup($id);
        
        if (! property_exists($data, 'name')) {
            throw new Exception\GroupCreationException("Missing field 'name'", 400);
        }
        
        $groupData = array(
            'id' => $id,
            'name' => $data->name,
            'description' => $data->description
        );
        
        $group = $this->getGroupsManager()->updateGroup(array(
            'group' => $groupData
        ));
        
        return $group;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::delete()
     */
    public function delete($id)
    {
        $group = $this->fetchGroup($id);
        
        return $this->deleteGroup($id);
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchMembers()
     */
    public function fetchMembers($id)
    {
        $group = $this->fetchGroup($id);
        
        return $this->fetchGroupMembers($id);
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchUserGroups()
     */
    public function fetchUserGroups($userId, Parameters $params)
    {
        $groupTypes = $this->extractGroupTypes($params);
        $vos = $this->getTypeToParentGroupMap()->typesToVos($groupTypes);
        
        $groups = $this->fetchUserGroupsFromVos($userId, $vos);
        if (null !== $groups) {
            $groups = $this->filterGroupCollectionByValidation($groups);
            $this->fixGroupTypes($groups);
            $groups = $this->filterGroupCollectionByType($groups, $groupTypes);
        }
        
        return $groups;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::addUserToGroup()
     */
    public function addUserToGroup($userId, $groupId)
    {
        $group = $this->fetchGroup($groupId);
        $voId = $this->getVoIdByParentGroupId($group->getParentGroupId());
        $member = $this->getMemberByUser($userId, $voId);
        $this->addMemberToGroup($member, $group);
        
        return $member;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::removeUserFromGroup()
     */
    public function removeUserFromGroup($userId, $groupId)
    {
        $group = $this->fetchGroup($groupId);
        $voId = $this->getVoIdByParentGroupId($group->getParentGroupId());
        $member = $this->getMemberByUser($userId, $voId);
        $this->removeMemberFromGroup($member, $group);
        
        return true;
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchGroupAdmins()
     */
    public function fetchAdmins($groupId)
    {
        $group = $this->fetchGroup($groupId);
        $users = $this->fetchGroupAdmins($group);
        
        return $users;
    }


    /**
     * {@inhertidoc}
     * @see \PerunWs\Group\Service\ServiceInterface::addGroupAdmin()
     */
    public function addAdmin($groupId, $userId)
    {
        $group = $this->fetchGroup($groupId);
        $this->addAdminToGroup($userId, $group);
        
        return true;
    }


    /**
     * {@inhertidoc}
     * @see \PerunWs\Group\Service\ServiceInterface::removeGroupAdmin()
     */
    public function removeAdmin($groupId, $userId)
    {
        $group = $this->fetchGroup($groupId);
        $this->removeAdminFromGroup($userId, $group);
        
        return true;
    }


    /**
     * Retrieves the user's corresponding "member" entity.
     * 
     * @param integer $userId
     * @return \InoPerunApi\Entity\Member|null
     */
    public function getMemberByUser($userId, $voId)
    {
        try {
            $member = $this->getMembersManager()->getMemberByUser(array(
                'vo' => $voId,
                'user' => $userId
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\MemberRetrievalException(sprintf("User ID:%d not found in VO ID:%d", $userId, $voId), 400);
        }
        
        return $member;
    }


    /**
     * Returns a collection of groups by specific IDs.
     * 
     * @param array $groupIdList
     * @return GroupCollection
     */
    public function fetchByMultipleId(array $groupIdList)
    {
        $groups = new GroupCollection();
        
        foreach ($groupIdList as $groupId) {
            $group = $this->fetch($groupId);
            if (null !== $group) {
                $groups->append($group);
            }
        }
        
        return $groups;
    }


    /**
     * Filters the provided group collection using a list of group IDs.
     * 
     * @param GroupCollection $groups
     * @param array $filterIds
     * @return GroupCollection
     */
    public function filterGroupCollectionById(GroupCollection $groups, array $filterIds)
    {
        $filteredGroups = array();
        foreach ($groups as $group) {
            /* @var $group \InoPerunApi\Entity\Group */
            if (in_array($group->getId(), $filterIds)) {
                $filteredGroups[] = $group;
            }
        }
        
        $groups->setEntities($filteredGroups);
        
        return $groups;
    }


    /**
     * The returned collection contains only groups of the specified type.
     *  
     * @param GroupCollection $groups
     * @param string $groupType
     * @return GroupCollection
     */
    public function filterGroupCollectionByType(GroupCollection $groups, array $groupTypes)
    {
        $filteredGroups = array();
        foreach ($groups as $group) {
            /* @var $group \InoPerunApi\Entity\Group */
            if (in_array($group->getType(), $groupTypes)) {
                $filteredGroups[] = $group;
            }
        }
        
        $groups->setEntities($filteredGroups);
        
        return $groups;
    }


    /**
     * Filters the provided group collection, leaving only the valid groups.
     * 
     * @param GroupCollection $groups
     * @return GroupCollection
     */
    public function filterGroupCollectionByValidation(GroupCollection $groups)
    {
        $filteredGroups = array();
        foreach ($groups as $group) {
            /* @var $group \InoPerunApi\Entity\Group */
            if ($this->isValidGroup($group)) {
                $filteredGroups[] = $group;
            }
        }
        
        $groups->setEntities($filteredGroups);
        
        return $groups;
    }


    /**
     * Returns true, if the group is valid:
     *   - if it's a subgroup of the base group
     * 
     * @param Group $group
     * @return boolean
     */
    public function isValidGroup(Group $group)
    {
        return $this->getTypeToParentGroupMap()->isValidParentGroup($group->getParentGroupId());
    }


    /**
     * Validates a group and if not valid, throws an exception.
     * 
     * @param Group $group
     * @throws Exception\InvalidGroupException
     */
    public function checkGroup(Group $group)
    {
        if (! $this->isValidGroup($group)) {
            throw new Exception\InvalidGroupException(sprintf("Invalid group ID:%d", $group->getId()), 400);
        }
    }


    /**
     * Fetches all groups of the provided group types.
     * @param array $groupTypes
     * @return GroupCollection|null
     */
    public function fetchAllGroupsByType(array $groupTypes)
    {
        $groups = null;
        foreach ($groupTypes as $groupType) {
            $parentGroupId = $this->getParentGroupIdByGroupType($groupType);
            
            $childGroups = $this->fetchChildGroups($parentGroupId);
            if (null === $groups) {
                $groups = $childGroups;
                continue;
            }
            
            $groups->appendCollection($childGroups);
        }
        
        return $groups;
    }


    public function fetchGroup($id, $fetchAdmins = false)
    {
        $groupsManager = $this->getGroupsManager();
        
        try {
            $group = $groupsManager->getGroupById(array(
                'id' => $id
            ));
        } catch (PerunErrorException $e) {
            $exception = new Exception\GroupRetrievalException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
            if (self::PERUN_EXCEPTION_GROUP_NOT_EXISTS === $e->getErrorName()) {
                $exception->setNotFound(true);
            }
            
            throw $exception;
        }
        
        if (! $this->isValidGroup($group)) {
            return null;
        }
        
        if ($fetchAdmins) {
            $admins = $this->fetchGroupAdmins($id);
            $group->setAdmins($admins);
        }
        
        return $group;
    }


    public function fetchChildGroups($parentGroupId)
    {
        /* @var $groups \InoPerunApi\Entity\Collection\GroupCollection */
        // FIXME - use try - catch
        $groups = $this->getGroupsManager()->getSubGroups(array(
            'parentGroup' => $parentGroupId
        ));
        
        return $groups;
    }


    public function fetchUserGroupsFromVos($userId, array $vos)
    {
        $allGroups = null;
        foreach ($vos as $voId) {
            $member = $this->getMemberByUser($userId, $voId);
            $groups = $this->fetchMemberGroups($member);
            
            if (null === $groups) {
                continue;
            }
            
            if (null === $allGroups) {
                $allGroups = $groups;
                continue;
            }
            
            $allGroups->appendCollection($groups);
        }
        
        return $allGroups;
    }


    public function createGroup(Group $group, $groupType)
    {
        try {
            $targetVoId = $this->getTypeToParentGroupMap()->typeToVo($groupType);
        } catch (\Exception $e) {
            throw new Exception\GroupGenericException($e->getMessage(), 400, $e);
        }
        
        try {
            $createdGroup = $this->getGroupsManager()->createGroup(array(
                'vo' => $targetVoId,
                'group' => $group
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupCreationException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
        
        $this->fixGroupType($createdGroup);
        
        return $createdGroup;
    }


    public function deleteGroup($id)
    {
        try {
            $this->getGroupsManager()->deleteGroup(array(
                'group' => $id
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupDeleteException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
        
        return true;
    }


    public function fetchGroupMembers($id)
    {
        try {
            $members = $this->getGroupsManager()->getGroupRichMembers(array(
                'group' => $id
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("Group ID:%d not found", $id), 400, $e);
        }
        
        return $members;
    }


    public function fetchMemberGroups(Member $member)
    {
        try {
            $groups = $this->getGroupsManager()->getMemberGroups(array(
                'member' => $member->getId()
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
        
        return $groups;
    }


    public function addMemberToGroup(Member $member, Group $group)
    {
        try {
            $this->getGroupsManager()->addMember(array(
                'group' => $group->getId(),
                'member' => $member->getId()
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
    }


    public function removeMemberFromGroup(Member $member, Group $group)
    {
        try {
            $this->getGroupsManager()->removeMember(array(
                'group' => $group->getId(),
                'member' => $member->getId()
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
    }


    public function fetchGroupAdmins(Group $group)
    {
        try {
            $users = $this->getGroupsManager()->getAdmins(array(
                'group' => $group->getId()
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
        
        return $users;
    }


    public function addAdminToGroup($userId, Group $group)
    {
        try {
            $this->getGroupsManager()->addAdmin(array(
                'group' => $group->getId(),
                'user' => $userId
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
    }


    public function removeAdminFromGroup($userId, Group $group)
    {
        try {
            $this->getGroupsManager()->removeAdmin(array(
                'group' => $group->getId(),
                'user' => $userId
            ));
        } catch (PerunErrorException $e) {
            throw new Exception\GroupGenericException(sprintf("[%s] %s", $e->getErrorName(), $e->getErrorMessage()), 400, $e);
        }
    }


    /**
     * Groups post-retrieval post-processing.
     * 
     * @param GroupCollection $groups
     * @param Parameters $params
     * @return GroupCollection
     */
    public function processGroups(GroupCollection $groups, Parameters $params)
    {
        $groups = $this->filterGroups($groups, $params);
        $this->fixGroupTypes($groups);
        
        return $groups;
    }


    /**
     * Applies filterings to the groups.
     * 
     * @param GroupCollection $groups
     * @param Parameters $params
     * @return GroupCollection
     */
    public function filterGroups(GroupCollection $groups, Parameters $params)
    {
        if (isset($params['filter_group_id']) && is_array($params['filter_group_id'])) {
            $groups = $this->filterGroupCollectionById($groups, $params['filter_group_id']);
        }
        
        return $groups;
    }


    /**
     * Adjusts group types for all groups in the collection.
     * 
     * @param GroupCollection $groups
     */
    public function fixGroupTypes(GroupCollection $groups)
    {
        foreach ($groups as $group) {
            $this->fixGroupType($group);
        }
    }


    /**
     * Adjusts the group type according to the parent group ID.
     * 
     * @param Group $group
     */
    public function fixGroupType(Group $group)
    {
        try {
            $groupType = $this->getTypeToParentGroupMap()->parentGroupToType($group->getParentGroupId());
        } catch (\Exception $e) {
            throw new Exception\GroupGenericException($e->getMessage(), 400);
        }
        
        $group->setType($groupType);
    }


    /**
     * Gets the parent group ID based on the group type.
     * 
     * @param string $groupType
     * @throws Exception\GroupGenericException
     * @return integer
     */
    public function getParentGroupIdByGroupType($groupType)
    {
        try {
            $parentGroupId = $this->getTypeToParentGroupMap()->typeToParentGroup($groupType);
        } catch (\Exception $e) {
            throw new Exception\GroupGenericException($e->getMessage(), 400);
        }
        
        return $parentGroupId;
    }


    /**
     * Returns the VO ID the provided group ID is in.
     * 
     * @param integer $groupId
     * @throws Exception\GroupGenericException
     * @return integer
     */
    public function getVoIdByParentGroupId($groupId)
    {
        try {
            $voId = $this->getTypeToParentGroupMap()->getGroupVo($groupId);
        } catch (\Exception $e) {
            throw new Exception\GroupGenericException($e->getMessage(), 400);
        }
        
        return $voId;
    }


    /**
     * Returns available group types - either from input parameters, or all types.
     * 
     * @param Parameters $params
     * @return array
     */
    protected function extractGroupTypes(Parameters $params)
    {
        $groupTypes = $params->get('filter_type');
        if (null === $groupTypes) {
            $groupTypes = $this->getTypeToParentGroupMap()->getAllTypes();
        }
        
        return $groupTypes;
    }
}
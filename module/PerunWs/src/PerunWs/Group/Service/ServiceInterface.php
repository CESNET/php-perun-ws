<?php

namespace PerunWs\Group\Service;

use Zend\Stdlib\Parameters;
use PerunWs\Perun;


/**
 * Group service interface.
 */
interface ServiceInterface extends Perun\Service\ServiceInterface
{


    /**
     * Retrieves a single group by its ID.
     * 
     * @param integer $id
     * @return \InoPerunApi\Entity\Group
     */
    public function fetch($id);


    /**
     * Retrieves all groups
     * 
     * @param Parameters $params
     * @return \InoPerunApi\Entity\Collection\GroupCollection
     */
    public function fetchAll(Parameters $params);


    /**
     * Creates a new group and returns it.
     * 
     * @param array $data
     * @return \InoPerunApi\Entity\Group
     */
    public function create($data);


    /**
     * Modifies partially the group and returns the new version.
     * 
     * @param integer $id
     * @param array $data
     * @return \InoPerunApi\Entity\Group
     */
    public function patch($id, $data);


    /**
     * Deletes the group.
     * 
     * @param integer $id
     * @return boolean
     */
    public function delete($id);


    /**
     * Returns the list of group's members.
     * 
     * @param integer $id
     * @return \InoPerunApi\Entity\Collection\UserCollection
     */
    public function fetchMembers($id);


    /**
     * Returns the list of user's groups.
     * 
     * @param integer $userId
     * @param Parameters $params
     * @return \InoPerunApi\Entity\Collection\GroupCollection
     */
    public function fetchUserGroups($userId, Parameters $params);


    /**
     * Adds the user to the group.
     * 
     * @param integer $userId
     * @param integer $groupId
     */
    public function addUserToGroup($userId, $groupId);


    /**
     * Removes the user from the group.
     * 
     * @param integer $userId
     * @param integer $groupId
     */
    public function removeUserFromGroup($userId, $groupId);


    /**
     * Returns a list of group's administrators.
     * 
     * @param integer $groupId
     */
    public function fetchAdmins($groupId);


    /**
     * Adds the user to the group's administrators list.
     * 
     * @param integer $groupId
     * @param integer $userId
     */
    public function addAdmin($groupId, $userId);


    /**
     * Removes the user from the group's administrators list.
     * 
     * @param integer $groupId
     * @param integer $userId
     */
    public function removeAdmin($groupId, $userId);
}
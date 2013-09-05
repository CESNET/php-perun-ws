<?php

namespace PerunWs\Group\Service;


/**
 * Group service interface.
 */
interface ServiceInterface
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
     * @return \InoPerunApi\Entity\Collection\GroupCollection
     */
    public function fetchAll();


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
     * Adds the user as a member of the group.
     * 
     * @param integer $groupId
     * @param integer $userId
     */
    public function addMember($groupId, $userId);


    /**
     * Removes the user from the group.
     * 
     * @param integer $groupId
     * @param integer $userId
     */
    public function removeMember($groupId, $userId);
}
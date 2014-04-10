<?php

namespace PerunWs\Group\Service;

use Zend\Stdlib\Parameters;
use PerunWs\Perun\Service\AbstractCachedService;


/**
 * Caching proxy service for the group service object.
 */
class CachedService extends AbstractCachedService implements ServiceInterface
{


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll(Parameters $params)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::create()
     */
    public function create($data)
    {
        /*
         * Invalidate:
         *   - fetchAll()
         */
        $this->invalidateCall('fetchAll', array());
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::patch()
     */
    public function patch($id, $data)
    {
        /*
         * Invalidate:
         *   - fetch($id)
         *   - fetchAll()
         */
        $this->invalidateCall('fetch', array(
            $id
        ));
        $this->invalidateCall('fetchAll', array());
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::delete()
     */
    public function delete($id)
    {
        /*
         * Invalidate:
         *   - fetch($id)
         *   - fetchAll()
         */
        $this->invalidateCall('fetch', array(
            $id
        ));
        $this->invalidateCall('fetchAll', array());
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchMembers()
     */
    public function fetchMembers($id)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchUserGroups()
     */
    public function fetchUserGroups($userId, Parameters $params)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::addUserToGroup()
     */
    public function addUserToGroup($userId, $groupId)
    {
        /*
         * Invalidate:
         *   - fetchMembers($groupId)
         *   - fetchUserGroups($userId)
         */
        $this->invalidateCall('fetchMembers', array(
            $groupId
        ));
        $this->invalidateCall('fetchUserGroups', array(
            $userId
        ));
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::removeUserFromGroup()
     */
    public function removeUserFromGroup($userId, $groupId)
    {
        /*
         * Invalidate:
         *   - fetchMembers($groupId)
         *   - fetchUserGroups($userId)
         */
        $this->invalidateCall('fetchMembers', array(
            $groupId
        ));
        $this->invalidateCall('fetchUserGroups', array(
            $userId
        ));
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inhertidoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchGroupAdmins()
     */
    public function fetchAdmins($groupId)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inhertidoc}
     * @see \PerunWs\Group\Service\ServiceInterface::addGroupAdmin()
     */
    public function addAdmin($groupId, $userId)
    {
        /*
         * Invalidate:
         *   - fetchGroupAdmins($groupId)
         */
        $this->invalidateCall('fetchGroupAdmins', array(
            $groupId
        ));
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inhertidoc}
     * @see \PerunWs\Group\Service\ServiceInterface::removeGroupAdmin()
     */
    public function removeAdmin($groupId, $userId)
    {
        /*
         * Invalidate:
         *   - fetchGroupAdmins($groupId)
         */
        $this->invalidateCall('fetchGroupAdmins', array(
            $groupId
        ));
        
        return $this->directCall(__FUNCTION__, func_get_args());
    }
}
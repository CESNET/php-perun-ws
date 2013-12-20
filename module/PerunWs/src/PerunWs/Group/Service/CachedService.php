<?php

namespace PerunWs\Group\Service;

use PerunWs\Perun\Service\AbstractCachedService;


class CachedService extends AbstractCachedService implements ServiceInterface
{


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll()
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
        return $this->directCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::patch()
     */
    public function patch($id, $data)
    {
        // FIXME - invalidate/refresh cache
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
        // FIXME - invalidate cache
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
    public function fetchUserGroups($userId)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Group\Service\ServiceInterface::addUserToGroup()
     */
    public function addUserToGroup($userId, $groupId)
    {
        // FIXME - invalidate/refresh cache
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
        // FIXME - invalidate/refresh cache
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
}
<?php

namespace PerunWs\Group\User;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PerunWs\Group\Service\ServiceInterface;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\HalResource;


/**
 * Group's users resource listener.
 */
class Listener extends AbstractListenerAggregate
{

    /**
     * @var ServiceInterface
     */
    protected $service;


    /**
     * Constructor.
     * 
     * @param ServiceInterface $service
     */
    public function __construct(ServiceInterface $service)
    {
        $this->setService($service);
    }


    /**
     * @return ServiceInterface
     */
    public function getService()
    {
        return $this->service;
    }


    /**
     * @param ServiceInterface $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }


    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetchAll', array(
            $this,
            'onFetchAll'
        ));
        $this->listeners[] = $events->attach('update', array(
            $this,
            'onUpdate'
        ));
        $this->listeners[] = $events->attach('delete', array(
            $this,
            'onDelete'
        ));
    }


    /**
     * Returns all group members.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Collection\MemberCollection
     */
    public function onFetchAll(ResourceEvent $e)
    {
        $id = $e->getRouteParam('group_id');
        
        $users = $this->service->fetchMembers($id);
        // _dump($users);
        return $users;
    }


    /**
     * Adds a user to the group.
     * 
     * @param ResourceEvent $e
     * @return HalResource
     */
    public function onUpdate(ResourceEvent $e)
    {
        $groupId = $e->getRouteParam('group_id');
        $userId = $e->getRouteParam('user_id');
        
        $member = $this->service->addUserToGroup($userId, $groupId);
        
        $resource = new HalResource(
            array(
                'user_id' => $userId,
                'group_id' => $groupId
            ), $userId);
        
        return $resource;
    }


    /**
     * Removes a user from the group.
     * 
     * @param ResourceEvent $e
     * @return boolean
     */
    public function onDelete(ResourceEvent $e)
    {
        $groupId = $e->getRouteParam('group_id');
        $userId = $e->getRouteParam('user_id');
        
        return $this->service->removeUserFromGroup($userId, $groupId);
    }
}
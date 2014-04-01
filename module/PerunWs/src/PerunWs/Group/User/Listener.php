<?php

namespace PerunWs\Group\User;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\HalResource;
use PhlyRestfully\Exception\DomainException;
use PerunWs\Group\Service\ServiceInterface;
use PerunWs\Group\Service\Exception\GroupRetrievalException;
use PerunWs\Group\Service\Exception\MemberRetrievalException;


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
        
        try {
            $users = $this->service->fetchMembers($id);
        } catch (GroupRetrievalException $e) {
            throw new DomainException($e->getMessage(), 404, $e);
        }
        
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
        
        try {
            $member = $this->service->addUserToGroup($userId, $groupId);
        } catch (MemberRetrievalException $e) {
            throw new DomainException($e->getMessage(), 400, $e);
        }
        
        $resource = new HalResource(array(
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
        
        try {
            return $this->service->removeUserFromGroup($userId, $groupId);
        } catch (MemberRetrievalException $e) {
            throw new DomainException($e->getMessage(), 400, $e);
        }
    }
}
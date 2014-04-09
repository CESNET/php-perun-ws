<?php

namespace PerunWs\Group\Admin;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\HalResource;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;
use PerunWs\Group\Service\Exception\UserNotAdminException;
use PerunWs\Group\Service\Exception\UserAlreadyAdminException;
use PerunWs\Group\Service\Exception\GroupRetrievalException;
use PerunWs\Group\Service\ServiceInterface;


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
     * {@inhertidoc}
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
     * Returns all administrators for the group.
     * 
     * @param ResourceEvent $event
     * @throws DomainException
     * @return \InoPerunApi\Entity\Collection\UserCollection
     */
    public function onFetchAll(ResourceEvent $event)
    {
        $groupId = $event->getRouteParam('group_id');
        
        return $this->getService()->fetchAdmins($groupId);
    }


    /**
     * Adds the user to the group's administrators list.
     * 
     * @param ResourceEvent $event
     * @throws DomainException
     * @return \PhlyRestfully\HalResource
     */
    public function onUpdate(ResourceEvent $event)
    {
        $groupId = $event->getRouteParam('group_id');
        $userId = $event->getRouteParam('user_id');
        
        $this->getService()->addAdmin($groupId, $userId);
        
        $resource = new HalResource(array(
            'user_id' => $userId,
            'group_id' => $groupId
        ), $userId);
        
        return $resource;
    }


    /**
     * Removes the user from the group's administrators list.
     * 
     * @param ResourceEvent $event
     * @throws DomainException
     * @return boolean
     */
    public function onDelete(ResourceEvent $event)
    {
        $groupId = $event->getRouteParam('group_id');
        $userId = $event->getRouteParam('user_id');
        
        $this->getService()->removeAdmin($groupId, $userId);
        
        return true;
    }
}
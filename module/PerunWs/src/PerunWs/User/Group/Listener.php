<?php

namespace PerunWs\User\Group;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\ResourceEvent;
use PerunWs\Group\Service\ServiceInterface;


/**
 * User groups resource listener.
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
    }


    /**
     * Returns all groups, the user is member od.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Collection\GroupCollection
     */
    public function onFetchAll(ResourceEvent $e)
    {
        $userId = $e->getRouteParam('user_id');
        $groups = $this->service->fetchUserGroups($userId);
        
        return $groups;
    }
}
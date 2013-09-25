<?php

namespace PerunWs\User\Group;

use PhlyRestfully\Exception\RuntimeException;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\ResourceEvent;
use PerunWs\Group\Service\ServiceInterface;
use PerunWs\Group\Service\Exception\MemberRetrievalException;


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
        
        try {
            $groups = $this->service->fetchUserGroups($userId);
        } catch (MemberRetrievalException $e) {
            throw new RuntimeException($e->getMessage(), 400, $e);
        }
        
        return $groups;
    }
}
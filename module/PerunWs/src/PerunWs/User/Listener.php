<?php

namespace PerunWs\User;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;
use PerunWs\User\Service\Service;
use InoPerunApi\Entity\Collection\Collection;
use PhlyRestfully\HalCollection;


/**
 * User resource listener.
 */
class Listener extends AbstractListenerAggregate
{

    /**
     * @var Service
     */
    protected $service;


    /**
     * Constructor.
     * 
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }


    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetch', array(
            $this,
            'onFetch'
        ));
        $this->listeners[] = $events->attach('fetchAll', array(
            $this,
            'onFetchAll'
        ));
    }


    /**
     * Returns a single user by ID.
     * 
     * @param ResourceEvent $e
     * @throws DomainException
     * @return \InoPerunApi\Entity\User
     */
    public function onFetch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $user = $this->service->fetch($id);
        if (! $user) {
            throw new DomainException('User not found', 404);
        }
        
        return $user;
    }


    /**
     * Returns all users, optionally filtered by a search string.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Collection\UserCollection
     */
    public function onFetchAll(ResourceEvent $e)
    {
        $params = array();
        
        $search = $e->getQueryParam('search');
        if (preg_match('/^\w+$/', $search)) {
            $params['searchString'] = $search;
        }
        
        $users = $this->service->fetchAll($params);
        
        return $users;
    }
}
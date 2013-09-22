<?php

namespace PerunWs\Group;

use PhlyRestfully\Exception\InvalidArgumentException;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;
use PerunWs\Group\Service\ServiceInterface;


/**
 * Group resource listener.
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
     * @param ServiceInterface $service
     */
    public function setService(ServiceInterface $service)
    {
        $this->service = $service;
    }


    /**
     * @return ServiceInterface
     */
    public function getService()
    {
        return $this->service;
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
        $this->listeners[] = $events->attach('create', array(
            $this,
            'onCreate'
        ));
        $this->listeners[] = $events->attach('patch', array(
            $this,
            'onPatch'
        ));
        $this->listeners[] = $events->attach('delete', array(
            $this,
            'onDelete'
        ));
    }


    /**
     * Returns a single group.
     * 
     * @param ResourceEvent $e
     * @throws DomainException
     * @return \InoPerunApi\Entity\Group
     */
    public function onFetch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        if (! $id) {
            throw new InvalidArgumentException('Missing the "id" parameter', 400);
        }
        
        $group = $this->service->fetch($id);
        if (! $group) {
            throw new DomainException(sprintf("Group ID:%d not found", $id), 404);
        }
        return $group;
    }


    /**
     * Returns all groups.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Collection\GroupCollection
     */
    public function onFetchAll(ResourceEvent $e)
    {
        $groups = $this->service->fetchAll();
        
        return $groups;
    }


    /**
     * Creates a new group.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Group
     */
    public function onCreate(ResourceEvent $e)
    {
        $data = $e->getParam('data');
        $newGroup = $this->service->create($data);
        
        return $newGroup;
    }


    /**
     * Updates an existing group.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Group
     */
    public function onPatch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $data = $e->getParam('data');
        
        $group = $this->service->patch($id, $data);
        return $group;
    }


    /**
     * Deletes a group.
     * 
     * @param ResourceEvent $e
     * @return boolean
     */
    public function onDelete(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        return $this->service->delete($id);
    }
}
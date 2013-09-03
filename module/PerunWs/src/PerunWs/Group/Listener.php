<?php

namespace PerunWs\Group;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\ResourceEvent;
use PerunWs\Group\Service\ServiceInterface;


class Listener extends AbstractListenerAggregate
{

    /**
     * @var ServiceInterface
     */
    protected $service;


    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
    }


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


    public function onFetch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $group = $this->service->fetch($id);
        if (! $group) {
            throw new DomainException('Group not found', 404);
        }
        return $group;
    }


    public function onFetchAll(ResourceEvent $e)
    {
        $groups = $this->service->fetchAll();
        
        return $groups;
    }


    public function onCreate(ResourceEvent $e)
    {
        $data = $e->getParam('data');
        $newGroup = $this->service->create($data);
        
        return $newGroup;
    }


    public function onPatch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $data = $e->getParam('data');
        
        $group = $this->service->patch($id, $data);
        return $group;
    }


    public function onDelete(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        return $this->service->delete($id);
    }
}
<?php

namespace PerunWs\User;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;


class Listener extends AbstractListenerAggregate
{

    /**
     * @var Storage
     */
    protected $storage;


    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
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
    }


    public function onFetch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $user = $this->storage->fetch($id);
        if (! $user) {
            throw new DomainException('User not found', 404);
        }
        return $user;
    }


    public function onFetchAll($e)
    {
        return $this->storage->fetchAll();
    }
}
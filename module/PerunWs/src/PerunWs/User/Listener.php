<?php

namespace PerunWs\User;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;
use PerunWs\User\Service\Service;
use InoPerunApi\Entity\Collection\Collection;
use PhlyRestfully\HalCollection;


class Listener extends AbstractListenerAggregate
{

    /**
     * @var Storage
     */
    protected $service;


    public function __construct(Service $service)
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
    }


    public function onFetch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $user = $this->service->fetch($id);
        if (! $user) {
            throw new DomainException('User not found', 404);
        }
        return $user;
    }


    public function onFetchAll(ResourceEvent $e)
    {
        $params = array(
            'vo' => 421
        );
        $search = $e->getQueryParam('search');
        if (preg_match('/^\w+$/', $search)) {
            $params['searchString'] = $search;
        }
        
        $users = $this->service->fetchAll($params);
        return $users;
        $data = array();
        $hydrator = new Hydrator();
        if ($users instanceof Collection) {
            foreach ($users as $user) {
                $data[] = $hydrator->extract($user->getUser());
            }
        }
        
        $collection = new HalCollection($data);
        $collection->setAttributes(array(
            'count' => 2
        ));
        /*

        */
        _dump($collection);
        
        return $collection;
    }
}
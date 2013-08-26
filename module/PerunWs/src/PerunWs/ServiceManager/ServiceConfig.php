<?php

namespace PerunWs\ServiceManager;

use Zend\ServiceManager\Config;
use PerunWs\User;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            'PerunWs\UserStorage' => function ($services)
            {
                $storage = new User\Storage();
                return $storage;
            },
            
            'PerunWs\UserListener' => function ($services)
            {
                return new User\Listener($services->get('PerunWs\UserStorage'));
            }
        );
    }
}
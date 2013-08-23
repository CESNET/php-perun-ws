<?php

namespace PerunWs\ServiceManager;

use Zend\ServiceManager\Config;


class ControllerConfig extends Config
{


    public function getFactories()
    {
        return array(
            'PerunWs\UserController' => function ($controllers)
            {},
            
            'PerunWs\UserGroupsController' => function ($controllers)
            {},
            
            'PerunWs\GroupController' => function ($controllers)
            {},
            
            'PerunWs\GroupUsersController' => function ($controllers)
            {}
        );
    }
}
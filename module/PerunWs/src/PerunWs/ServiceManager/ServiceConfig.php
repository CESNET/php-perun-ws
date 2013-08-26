<?php

namespace PerunWs\ServiceManager;

use Zend\ServiceManager\Config;
use PerunWs\User;
use InoPerunApi\Client\ClientFactory;
use InoPerunApi\Manager\GenericManager;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            
            'PerunWs\Client' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_api']) || ! is_array($config['perun_api'])) {
                    throw new Exception\MissingConfigException('perun_api');
                }
                
                $clientConfig = $config['perun_api'];
                
                $clientFactory = new ClientFactory();
                $client = $clientFactory->createClient($clientConfig);
                
                return $client;
            },
            
            'PerunWs\UserManager' => function ($services)
            {
                $client = $services->get('PerunWs\Client');
                $userManager = new GenericManager($client);
                $userManager->setManagerName('usersManager');
                
                return $userManager;
            },
            
            'PerunWs\UserStorage' => function ($services)
            {
                $manager = $services->get('PerunWs\UserManager');
                $storage = new User\Storage($manager);
                return $storage;
            },
            
            'PerunWs\UserListener' => function ($services)
            {
                return new User\Listener($services->get('PerunWs\UserStorage'));
            },
            
            'PerunWs\UserGroupsListener' => function ($services) {
                
            }
        );
    }
}
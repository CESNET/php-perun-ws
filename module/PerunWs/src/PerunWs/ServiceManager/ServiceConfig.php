<?php

namespace PerunWs\ServiceManager;

use Zend\ServiceManager\Config;
use PerunWs\User;
use InoPerunApi\Client\ClientFactory;
use InoPerunApi\Manager\Factory\GenericFactory;
use PerunWs\Hydrator\DefaultHydrator;
use Zend\ServiceManager\ServiceManager;


class ServiceConfig extends Config
{


    public function getInvokables()
    {
        return array(
            'PPerunWs\DefaultHydrator' => 'PerunWs\Hydrator\DefaultHydrator'
        );
    }


    public function getFactories()
    {
        return array(
            
            'PerunWs\DefaultHydrator' => function ($services)
            {
                return new DefaultHydrator();
            },
            
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
            
            'PerunWs\EntityManagerFactory' => function ($services)
            {
                $client = $services->get('PerunWs\Client');
                $factory = new GenericFactory($client);
                return $factory;
            },
            
            'PerunWs\UserService' => function ($services)
            {
                $entityManagerFactory = $services->get('PerunWs\EntityManagerFactory');
                
                $service = new User\Service\Service();
                $service->setEntityManagerFactory($entityManagerFactory);
                return $service;
            },
            
            'PerunWs\UserListener' => function ($services)
            {
                return new User\Listener($services->get('PerunWs\UserService'));
            },
            
            'PerunWs\UserGroupsListener' => function ($services)
            {}
        );
    }
}
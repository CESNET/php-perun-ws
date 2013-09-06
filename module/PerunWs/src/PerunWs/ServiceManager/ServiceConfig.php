<?php

namespace PerunWs\ServiceManager;

use Zend\ServiceManager\Config;
use PerunWs\User;
use PerunWs\Group;
use PerunWs\Hydrator\DefaultHydrator;
use InoPerunApi\Client\ClientFactory;
use InoPerunApi\Manager\Factory\GenericFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;


class ServiceConfig extends Config
{


    public function getFactories()
    {
        return array(
            
            'PerunWs\DefaultHydrator' => function ($services)
            {
                return new DefaultHydrator();
            },
            
            /**
             * Perun API client 
             */
            'PerunWs\Client' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_ws']) || ! is_array($config['perun_ws'])) {
                    throw new Exception\MissingConfigException('perun_ws');
                }
                
                $perunWsConfig = $config['perun_ws'];
                if (! isset($perunWsConfig['perun_api']) || ! is_array($perunWsConfig['perun_api'])) {
                    throw new Exception\MissingConfigException('perun_ws/perun_api');
                }
                
                $clientConfig = $perunWsConfig['perun_api'];
                
                $clientFactory = new ClientFactory();
                $client = $clientFactory->createClient($clientConfig);
                
                return $client;
            },
            
            /**
             * Perun API entity manager factory
             */
            'PerunWs\EntityManagerFactory' => function ($services)
            {
                $client = $services->get('PerunWs\Client');
                $factory = new GenericFactory($client);
                return $factory;
            },
            
            /**
             * Perun user service
             */
            'PerunWs\UserService' => function ($services)
            {
                $entityManagerFactory = $services->get('PerunWs\EntityManagerFactory');
                
                $service = new User\Service\Service($services->get('PerunWs\ServiceParameters'));
                $service->setEntityManagerFactory($entityManagerFactory);
                return $service;
            },
            
            /**
             * Perun group service
             */
            'PerunWs\GroupService' => function ($services)
            {
                $entityManagerFactory = $services->get('PerunWs\EntityManagerFactory');
                
                $service = new Group\Service\Service($services->get('PerunWs\ServiceParameters'));
                $service->setEntityManagerFactory($entityManagerFactory);
                return $service;
            },
            
            /**
             * Perun user resource listener
             */
            'PerunWs\UserListener' => function ($services)
            {
                return new User\Listener($services->get('PerunWs\UserService'));
            },
            
            /**
             * Perun user's groups resource listener
             */
            'PerunWs\UserGroupsListener' => function ($services)
            {
                return new User\Group\Listener($services->get('PerunWs\GroupService'));
            },
            
            'PerunWs\GroupsListener' => function ($services)
            {
                return new Group\Listener($services->get('PerunWs\GroupService'));
            },
            
            /**
             * Perun group's users resource listener
             */
            'PerunWs\GroupUsersListener' => function ($services)
            {
                return new Group\User\Listener($services->get('PerunWs\GroupService'));
            },
            
            /**
             * Global service parameters
             */
            'PerunWs\ServiceParameters' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['perun_service']) || ! is_array($config['perun_ws']['perun_service'])) {
                    throw new Exception\MissingConfigException('perun_ws/perun_service');
                }
                
                return new Parameters($config['perun_ws']['perun_service']);
            }
        );
    }
}
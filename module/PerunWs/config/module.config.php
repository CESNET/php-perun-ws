<?php
return array(
    
    'router' => array(
        'routes' => array(
            
            /*
             * /users/{id}
             */
            'users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/users[/:id]',
                    // 'controller' => 'PerunWs\UserController',
                    'defaults' => array(
                        'controller' => 'PerunWs\UserController'
                    )
                ),
                
                'may_terminate' => true,
                'child_routes' => array(
                    
                    /*
                     * /users/{id}/groups
                     */
                    'user-groups' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/groups',
                            'defaults' => array(
                                'controller' => 'PerunWs\UserGroupsController'
                            )
                        )
                    )
                )
            )
            ,
            
            /*
             * /groups/{group_id}
             */
            'groups' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/groups[/:group_id]',
                    'controller' => 'PerunWs\GroupController'
                ),
                'may_terminate' => true,
                
                /*
                 * /groups/{group_id}/users/{user_id}
                 */
                'child_routes' => array(
                    'group-users' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/users[/:user_id]',
                            'controller' => 'PerunWs\GroupUsersController'
                        )
                    )
                )
            )
        )
    ),
    
    'phlyrestfully' => array(
        
        'resources' => array(
            
            'PerunWs\UserController' => array(
                'identifier' => 'Users',
                'listener' => 'PerunWs\UserListener',
                'resource_identifiers' => array(
                    'UserResource'
                ),
                'collection_http_options' => array(
                    'get'
                ),
                'collection_name' => 'users',
                'page_size' => 10,
                'resource_http_options' => array(
                    'get'
                ),
                'route_name' => 'users'
            ),
            
            'PerunWs\UserGroupsController' => array(
                'identifier' => 'UserGroups',
                'listener' => 'PerunWs\UserGroupsListener',
                'resource_identifiers' => array(
                    'UserGroupsResource'
                ),
                'collection_http_options' => array(
                    'get'
                ),
                'collection_name' => 'groups',
                'page_size' => 10,
                'resource_http_options' => array(
                    'get'
                ),
                'route_name' => 'users/user-groups'
            )
        )
    ),
    
    'perun_api' => array(
        
        'client' => array(
            'url' => 'https://perun.example.org/api/'
        ),
        
        'http_client' => array(
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'useragent' => 'Perun Client',
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_CAINFO => '/etc/ssl/certs/ca-bundle.pem'
            )
        ),
        
        'authenticator' => array(
            'class' => 'InoPerunApi\Client\Authenticator\ClientCertificate',
            'options' => array(
                'key_file' => '/etc/ssl/private/key.pem',
                'crt_file' => '/etc/ssl/certs/crt.pem'
            )
        )
    )
);
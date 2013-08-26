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
            )
        )
    )
);
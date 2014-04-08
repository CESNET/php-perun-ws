FORMAT: 1A

# Shongo Perun Web Service

## Authorization

The web service can be used by registered clients only. Upon registration the client receives a secret. The client then must provide this secret in every request. The secret needs to be passed in the _Authorization_ HTTP request header.

    GET /users HTTP/1.1
    Accept: application/json
    Authorization: dca51ae20ef3039012a4e1e606439780
    
## Caching

The service automatically caches the records it fetches from Perun for some time. If you need to get current data, you may force the web service to fetch data 
from Perun even if there is an unexpired local copy. You can do this by setting the _Cache-Control_ HTTP header for the current request:

    Cache-Contro: no-cache

## Error reporting

In case of an error response (status codes 4xx, 5xx), the body of the response will contain 
details about the error according to the [API Problem specification](http://tools.ietf.org/html/draft-nottingham-http-problem-04)
with the following properties ([source](https://phlyrestfully.readthedocs.org/en/latest/problems.html)):

* __describedBy__ - a URL to a document describing the error condition (required)
* __title__ a brief title for the error condition (required)
* __httpStatus__ - the HTTP status code for the current request (optional)
* __detail__ error details specific to this request (optional)
* __supportId__ a URL to the specific problem occurrence (e.g., to a log message) (optional)

Example:

    HTTP/1.1 500 Internal Error
    Content-Type: application/api-problem+json

    {
        "describedBy": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
        "detail": "Status failed validation",
        "httpStatus": 500,
        "title": "Internal Server Error"
    }


# Group User
User resources.

## User [/user/{id}]
A single user object.

+ Parameters
    + id (string) ... The user ID
    
+ Model (application/hal+json)
    + Body
        
        ```
        {
            "id" : 1,
            "first_name" : "Foo",
            "last_name" : "Bar",
            "display_name" : "Foo Bar",
            "organization" : "Foobar Inc.",
            "mail" : "foo@bar.org",
            "language" : "en",
            "member_id" : 123,
            "member_status" : "VALID",
            "principal_names" : [
                "foo@bar.org", "bar@foo.org"
            ],
            "authentication_info" : {
                "provider" : "https://idp.example.org/shibboleth",
                "instant" : "2013-12-23T06:37:05.149Z",
                "loa:" : 2
            },
            "_links" : {
                "self" : {
                    "href" : "/users/1"
                }
            }
        }
        ```

### Retrieve a Single User [GET]
+ Response 200

    [User][]
    

## User Groups [/user/{id}/groups]
The list of groups the user is member of.

+ Parameters
    + id (string) ... The user ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "count": 2,
            "total": 20,
            "_embedded": {
                "groups": [
                    {
                        "id": 456,
                        "name": "somegroup",
                        "unique_name": "rootgroup:somegroup",
                        "description": "Simple group description",
                        "_links": {
                            "self": {
                                "href": "/groups/456"
                            }
                        }
                    }
                ]
            },
            "_links": {
                "self": {
                    "href": "/users/123/groups"
                }
            }
        }
        ```

### Retrieve user's groups [GET]
+ Response 200

    [User Groups][]
    

## Users Collection [/users?search,filter_user_id]
Collection of all users.

+ Model (application/hal+json)
    + Body
        
        ```
        {
            "count": 2,
            "total": 12,
            "_embedded": {
                "users": [{
                    "id": 1,
                    "first_name": "Foo",
                    "last_name": "Bar",
                    "display_name": "Foo Bar",
                    "organization": "Foobar Inc.",
                    "mail": "foo@bar.org",
                    "language": "en",
                    "member_id": 123,
                    "member_status": "VALID",
                    "principal_names": [
                        "foo@bar.org",
                        "bar@foo.org"
                    ],
                    "authentication_info": {
                        "provider": "https://idp.example.org/shibboleth",
                        "instant": "2013-12-23T06:37:05.149Z",
                        "loa:": 2
                    },
                    "_links": {
                        "self": {
                            "href": "/users/1"
                        }
                    }
                }, {
                    "id": 2,
                    "first_name": "Franta",
                    "last_name": "Vomáčka",
                    "display_name": "Franta Vomáčka",
                    "mail": "franta@email.cz",
                    "member_id": 456,
                    "member_status": "VALID",
                    "principal_names": [
                        "vomacka@foo.cz",
                    ],
                    "authentication_info": {
                        "provider": "https://idp.example.org/shibboleth",
                        "instant": "2013-12-23T06:37:05.149Z",
                        "loa:": 2
                    },
                    "_links": {
                        "self": {
                            "href": "/users/2"
                        }
                    }
                }]
            },
            "_links": {
                "self": {
                    "href": "/users"
                }
            }
        }
        ```

### List all users [GET]
+ Parameters
    + search (string, optional) ... a sub-string to search for
    + filter_user_id (string, optional) ... comma separated user IDs to list
    
+ Response 200

    [Users Collection][]
    

# Group Group
Group resources

## Group [/groups/{id}]
A single group.

+ Parameters
    + id (integer) ... The group ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "id" : 456,
            "name" : "somegroup",
            "unique_name" : "rootgroup:somegroup",
            "description" : "Simple group description",
            "admins" : [
                {
                    "id" : 123,
                    "first_name" : "Some",
                    "last_name" : "Admin",
                    "_links" : {
                        "self" : {
                            "href" : "/users/123"
                        }
                    }
                }
            ],
            "_links" : {
                "self" : {
                    "href" : "/groups/456"
                }
            }
        }
        ```

### Retrieve a single group [GET]
+ Response 200

    [Group][]
    
### Modify a single group [PUT]
Updates a group.

+ Request (application/json)
    
    ```
    {
        "name": "New group name",
        "description": "New group description"
    }
    ```

+ Response 200

    [Group][]
    
### Delete a group [DELETE]
Deletes a single group.

+ Response 204


## Group users [/groups/{id}/users]
Group's members.

+ Parameters
    + id (integer) ... The group ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "count": 1,
            "total": 1,
            "_embedded": {
                "users": [
                    {
                        "id": 1,
                        "first_name": "Ivan",
                        "last_name": "Novakov",
                        "member_id": 123,
                        "member_status": "VALID",
                        "_links": {
                            "self": {
                                "href": "/users/1"
                            }
                        }
                    }
                ]
            },
            "_links": {
                "self": {
                    "href": "/groups/5/users"
                }
            }
        }
        ```

### Retrieve all group's members [GET]
+ Response 200

    [Group users][]
    
## Group user [/groups/{id}/users/{user_id}]
A specific group member.

+ Parameters
    + id (integer) ... The group ID
    + user_id (integer) ... The user ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "user_id": "123",
            "group_id": "456",
            "_links": {
                "self": {
                    "href": "/groups/123/users/456"
                }
            }
        }
        ```

### Add a user to a group [PUT]
+ Response 200

    [Group user][]
    
### Remove a user from a group [DELETE]
+ Response 204

## Groups Collection [/groups?filter_group_id]
Collection of all groups.

+ Model (application/hal+json)
    + Body
    
        ```
        {
            "count": 2,
            "total": 20,
            "_embedded": {
                "groups": [
                    {
                        "id": 456,
                        "name": "somegroup",
                        "unique_name": "rootgroup:somegroup",
                        "description": "Simple group description",
                        "_links": {
                            "self": {
                                "href": "/groups/456"
                            }
                        }
                    }
                ]
            },
            "_links": {
                "self": {
                    "href": "/groups"
                }
            }
        }
        ```

### List all groups [GET]
+ Parameters
    + filter_group_id (integer, optional) ... A comma-separated list of group IDs to list
    
+ Response 200

    [Groups Collection][]
    
### Greate a group [POST]

+ Request (application/json)

    ```
    {
        "name": "a new group",
        "description": "with optional description"
    }
    ```

+ Response 201

    [Group][]
    
## Group admins collection [/group/{id}/admins]
A list of group's administrators.

+ Parameters
    + id (integer) ... The group ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "count": 1,
            "total": 1,
            "_links": {
                "self": {
                    "href": "/groups/123/admins"
                }
            },
            "_embedded": {
                "admins": [
                    {
                        "id": 456,
                        "first_name": "Some",
                        "last_name": "User",
                        "_links": {
                            "self": {
                                "href": "/users/456"
                            }
                        }
                    }
                ]
            }
        }
        ```

### Get all group administrators [GET]
+ Response 200

    [Group admins collection][]
    
    
## Group admin [/groups/{id}/admins/{user_id}]
A single administrator resource.

+ Parameters
    + id (integer) ... The group ID
    + user_id (integer) ... The user ID
    
+ Model (application/hal+json)
    + Body
    
    
        ```
        {
            "user_id": 123,
            "group_id": 456,
            "_links": {
                "self": {
                    "href": "/groups/456/admins/123"
                }
            }
        }
        ```
    
### Add a user the group's administrators list [PUT]
+ Response 200

    [Group admin][]


### Remove a user from the group's administrators list [DELETE]
+ Response 204


# Group System Group
Systemgroup resources.

## System Group [/systemgroups/{id}]
A single systemgroup.

+ Parameters
    + id (integer) ... The group ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "id" : 456,
            "name" : "somegroup",
            "unique_name" : "rootgroup:somegroup",
            "description" : "Simple systemgroup description",
        
            "_links" : {
                "self" : {
                    "href" : "/systemgroups/456"
                }
            }
        }
        ```

### Retrieve a single systemgroup [GET]
+ Response 200

    [System Group][]
    
### Modify a single systemgroup [PUT]
Updates a group.

+ Request (application/json)
    
    ```
    {
        "name": "New systemgroup name",
        "description": "New systemgroup description"
    }
    ```

+ Response 200

    [Group][]
    
### Delete a systemgroup [DELETE]
Deletes a single group.

+ Response 204


## Systemgroup users [/systemgroups/{id}/users]
Systemgroup's members.

+ Parameters
    + id (integer) ... The group ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "count": 1,
            "total": 1,
            "_embedded": {
                "users": [
                    {
                        "id": 1,
                        "first_name": "Ivan",
                        "last_name": "Novakov",
                        "member_id": 123,
                        "member_status": "VALID",
                        "_links": {
                            "self": {
                                "href": "/users/1"
                            }
                        }
                    }
                ]
            },
            "_links": {
                "self": {
                    "href": "/systemgroups/5/users"
                }
            }
        }
        ```

### Retrieve all group's members [GET]
+ Response 200

    [Systemgroup users][]
    
## Systemgroup user [/systemgroups/{id}/users/{user_id}]
A specific systemgroup member.

+ Parameters
    + id (integer) ... The group ID
    + user_id (integer) ... The user ID
    
+ Model (application/hal+json)
    + Body
    
        ```
        {
            "user_id": "123",
            "group_id": "456",
            "_links": {
                "self": {
                    "href": "/systemgroups/123/users/456"
                }
            }
        }
        ```

### Add a user to a group [PUT]
+ Response 200

    [Systemgroup user][]
    
### Remove a user from a group [DELETE]
+ Response 204

## Systemgroups Collection [/systemgroups?filter_group_id]
Collection of all groups.

+ Model (application/hal+json)
    + Body
    
        ```
        {
            "count": 2,
            "total": 20,
            "_embedded": {
                "groups": [
                    {
                        "id": 456,
                        "name": "somegroup",
                        "unique_name": "rootgroup:somegroup",
                        "description": "Simple systemgroup description",
                        "_links": {
                            "self": {
                                "href": "/systemgroups/456"
                            }
                        }
                    }
                ]
            },
            "_links": {
                "self": {
                    "href": "/systemgroups"
                }
            }
        }
        ```

### List all groups [GET]
+ Parameters
    + filter_group_id (integer, optional) ... A comma-separated list of group IDs to list
    
+ Response 200

    [Systemgroups Collection][]
    
### Greate a group [POST]

+ Request (application/json)

    ```
    {
        "name": "a new systemgroup",
        "description": "with optional description"
    }
    ```

+ Response 201

    [Group][]

    
# Group Principal
Principal resources.

## Single principal [/principal/{name}]

+ Model (application/hal+json)
    + Body
    
        ```
        {
            "id": 123,
            "first_name": "Some",
            "last_name": "Principal",
            "_links": {
                "self": {
                    "href": "/principal/some.principal@example.org"
                },
                "user": {
                    "href": "/users/123"
                }
            }
        }
        ```

### Get user by principal name [GET]
+ Response 200

    [Single principal][]
    


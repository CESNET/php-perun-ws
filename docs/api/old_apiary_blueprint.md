HOST: https://shongo-auth.cesnet.cz/devel/perun/

--- Shongo Perun Web Service ---

---

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

---



--
User Resources
--

List all users.

Query parameters:

* __filter_user_id__ - comma separated user IDs - the resulting collection will contain the selected users only
* __search__ (optional) - a search (sub) string to filter the results

GET /users{?search}
< 200
< Content-Type: application/json
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
                "vomacka@foo.cz"
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

Get a single user
GET /users/{id}
< 200
< Content-Type: application/json
{
    "id": 1,
    "first_name": "Foo",
    "last_name": "Bar",
    "display_name": "Foo Bar",
    "email": "foo@bar.org",
    "phone": "+420 111 222 333",
    "organization": "Foobar Inc.",
    "language": "en",
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
}

Get user's groups
GET /users/{id}/groups
< 200
< Content-Type: application/json
{
    "count": 2,
    "total": 5,
    "_embedded": {
        "groups": [{
            "id": 10,
            "name": "first group",
            "description": "The First Group",
            "parent_group_id": null,
            "_links": {
                "self": {
                    "href": "/groups/10"
                }
            }
        }, {
            "id": 11,
            "name": "second group",
            "description": "The Second Group",
            "parent_group_id": 4,
            "_links": {
                "self": {
                    "href": "/groups/11"
                }
            }
        }]
    }
}

-- Group Resources --

List all groups

Query parameters:

* __filter_group_id__ - comma separated list of group IDs - the resulting collection will contain the selected groups only

GET /groups
< 200
< Content-Type: application/json
{
    "count": 2,
    "total": 20,
    "_embedded": {
        "groups": [{
            "id": 1,
            "name": "some group",
            "description": "Some group's description",
            "parent_group_id": null,
            "_links": {
                "self": {
                    "href": "/groups/1"
                }
            }
        }, {
            "id": 2,
            "name": "another group",
            "description": "Another group's description",
            "parent_group_id": null,
            "_links": {
                "self": {
                    "href": "/groups/2"
                }
            }
        }]
    },
    "_links": {
        "self": {
            "href": "/groups"
        }
    }
}

Get a single group
GET /groups/{id}
< 200
< Content-Type: application/json
{
    "id": 4,
    "name": "test group",
    "description": "Group for testing",
    "parent_group_id": null,
    "_links": {
        "self": {
            "href": "/groups/4"
        }
    }
}

Create a group
POST /groups
> Content-Type: application/json
{
    "name": "new group",
    "description": "New Group description"
}
< 201
< Content-Type: application/json
{
    "id": 21,
    "name": "new group",
    "description": "New Group description",
    "parent_group_id": null,
    "_links": {
        "self": {
            "href": "/groups/21"
        }
    }
}

Modify a group

__( ! ) Currently throws an error - Perun integration problem.__
PUT /groups/{id}
> Content-Type: application/json
{
    "name": "New group name",
    "description": "New group description"
}
< 200
< Content-Type: application/json
{
    "id": 4,
    "name": "New group name",
    "description": "New group description",
    "_links": {
        "self": {
            "href": "/groups/4"
        }
    }
}

Delete a group
DELETE /groups/{id}
< 204


List users in a group
GET /groups/{id}/users
< 200
< Content-Type: application/json
{
    "count": 1,
    "total": 1,
    "_embedded": {
        "users": [{
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
        }]
    },
    "_links": {
        "self": {
            "href": "/groups/5/users"
        }
    }
}

Add a user to the group
PUT /groups/{group_id}/users/{user_id}
< 200
< Content-Type: application/json
{
    "user_id": 4,
    "group_id": 12,
}


Remove a user from the group
DELETE /groups/{group_id}/users/{user_id}
< 204

-- Principal resources --

Get a user by principal name (eduPersonPrincipalName).
GET /principal/{principal_name}
< 200
< Content-Type: application/json
{
    "id": 123,
    "first_name": "Foo",
    "last_name": "Bar",
    "_links": {
        "self": {
            "href": "/principal/foo@bar.org"
        },
        "user": {
            "href": "/users/123"
        }
    }
}

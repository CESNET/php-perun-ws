# Perun remote calls

## User resource

### GET /users

Get all users (from VO)
* membersManager/getRichMembersWithAttributes vo=123

Find users with search
* membersManager/findRichMembersWithAttributesInVo vo=123, searchString=foo

### GET /users/{id}

Get single user by ID
* usersManager/getRichUserWithAttributes user=123

### GET /users/{id}/groups

Get user's groups
* groupsManager/getAllMemberGroups id=<member ID>

Get member ID from user ID:
* membersManager/getMemberByUser id=<user ID>


## Group resource

### GET /groups

Get all groups (in VO)
* groupsManager/getGroups (getAllGroups ?) vo=123

### GET /groups/{id}

Get specific group
* groupsManager/getGroupById id=<group ID>

### POST /groups

Create a new group
* groupsManager/createGroup vo=123, group=<new group entity>

### PUT /groups/{id}
* groupsManager/updateGroup vo=123, group=<group entity>

### DELETE /groups/{id}

Delete a group
* groupsManager/deleteGroup vo=123, group=<group entity>, forceDelete=<bool>

### GET /groups/{id}/users

Get all members of a group:
* groupsManager/getGroupMembers (getGroupRichMembers) id=<group ID>


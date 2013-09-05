# Perun remote calls

## User resource

### GET /users

Get all users (from VO)
* membersManager/getRichMembersWithAttributes vo=<VO ID> --> RichMember

Find users with search
* membersManager/findRichMembersWithAttributesInVo vo=<VO ID>, searchString=<string> --> RichMember

### GET /users/{id}

Get single user by ID
* usersManager/getRichUserWithAttributes user=<user ID> --> RichUser

### GET /users/{id}/groups

Get member ID from user ID:
* membersManager/getMemberByUser vo=<VO ID>, user=<user ID> --> Member

Get user's groups
* groupsManager/getAllMemberGroups id=<member ID> --> Group


## Group resource

### GET /groups

Get all groups (in VO)
* groupsManager/getGroups (getAllGroups ?) vo=<VO ID> --> Group

### GET /groups/{id}

Get specific group
* groupsManager/getGroupById id=<group ID> --> Group

### POST /groups

Create a new group
* groupsManager/createGroup vo=<VO ID>, group=<new group entity>

### PUT/PATCH /groups/{id}
* groupsManager/updateGroup vo=<VO ID>, group=<group entity>

### DELETE /groups/{id}

Delete a group
* groupsManager/deleteGroup group=<group entity>, forceDelete=<bool>

### GET /groups/{id}/users

Get all members of a group:
* groupsManager/getGroupMembers (getGroupRichMembers) id=<group ID> --> Member (RichMember)

### PUT /groups/{group_id}/users/{user_id}

Get member ID from user ID:
* membersManager/getMemberByUser vo=<VO ID>, user=<user ID> --> Member

Add the user to the group
* groupsManager/addMember group=<group ID>, member=<member ID>

### DELETE /groups/{group_id}/users/{user_id}

Get member ID from user ID:
* membersManager/getMemberByUser vo=<VO ID>, user=<user ID> --> Member

Remove the user from the group
* groupsManager/removeMember group=<group ID>, member=<member ID>


<?php

namespace PerunWs\Group;


class TypeToParentGroupMap
{


    /**
     * Returns the default group type.
     * 
     * @return string
     */
    public function getDefaultType()
    {}


    /**
     * Returns the corresponding parent group ID to the provided group type.
     * 
     * @param string $type
     * @return integer
     */
    public function typeToParentGroup($type)
    {}


    /**
     * Returns the corresponding VO ID to the provided group type.
     * 
     * @param string $type
     * @return integer
     */
    public function typeToVo($type)
    {}


    /**
     * Returns the corresponding group type to the provided parent group ID.
     * 
     * @param integer $groupId
     * @return string
     */
    public function parentGroupToType($groupId)
    {}


    /**
     * Returns true, if the group ID is a valid parent group.
     * 
     * @param string $groupId
     * @return boolean
     */
    public function isValidParentGroup($groupId)
    {}
}
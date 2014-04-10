<?php

namespace PerunWs\Group;


class TypeToParentGroupMap
{

    /**
     * @var array
     */
    protected $mapDef;

    /**
     * @var array
     */
    protected $reversedMapDef;


    /**
     * Constructor.
     * 
     * @param array $map
     */
    public function __construct(array $mapDef)
    {
        $this->setMapDef($mapDef);
    }


    /**
     * @return array
     */
    public function getMapDef()
    {
        return $this->mapDef;
    }


    /**
     * @param array $map
     */
    public function setMapDef(array $mapDef)
    {
        $this->mapDef = $mapDef;
    }


    /**
     * @return string
     */
    public function getDefaultType()
    {
        reset($this->mapDef);
        return key($this->mapDef);
    }


    /**
     * Returns all defined group types.
     * 
     * @return array
     */
    public function getAllTypes()
    {
        return array_keys($this->mapDef);
    }


    /**
     * Returns the corresponding parent group ID to the provided group type.
     * 
     * @param string $type
     * @return integer
     */
    public function typeToParentGroup($type)
    {
        if (! isset($this->mapDef[$type])) {
            throw new \InvalidArgumentException(sprintf("Unknown group type '%s'", $type));
        }
        
        if (! isset($this->mapDef[$type]['group_id'])) {
            throw new \RuntimeException(sprintf("Missing group ID for type '%s'", $type));
        }
        
        return $this->mapDef[$type]['group_id'];
    }


    /**
     * Returns the corresponding VO ID to the provided group type.
     * 
     * @param string $type
     * @return integer
     */
    public function typeToVo($type)
    {
        if (! isset($this->mapDef[$type])) {
            throw new \InvalidArgumentException(sprintf("Unknown group type '%s'", $type));
        }
        
        if (! isset($this->mapDef[$type]['vo_id'])) {
            throw new \RuntimeException(sprintf("Missing VO ID for type '%s'", $type));
        }
        
        return $this->mapDef[$type]['vo_id'];
    }


    /**
     * Returns the corresponding group type to the provided parent group ID.
     * 
     * @param integer $groupId
     * @return string
     */
    public function parentGroupToType($groupId)
    {
        $reversedMapDef = $this->getReversedMapDef();
        
        if (! isset($reversedMapDef[$groupId])) {
            throw new \InvalidArgumentException(sprintf("Unknown group ID '%d'", $groupId));
        }
        
        return $reversedMapDef[$groupId];
    }


    /**
     * Returns true, if the group ID is a valid parent group.
     * 
     * @param string $groupId
     * @return boolean
     */
    public function isValidParentGroup($groupId)
    {
        try {
            $type = $this->parentGroupToType($groupId);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        
        return true;
    }


    /**
     * Returns the "reversed" map definition, mapping "group ID --> type".
     * 
     * @return array
     */
    protected function getReversedMapDef()
    {
        if (null === $this->reversedMapDef) {
            $this->reversedMapDef = array();
            foreach ($this->mapDef as $type => $fields) {
                if (isset($fields['group_id'])) {
                    $this->reversedMapDef[$fields['group_id']] = $type;
                }
            }
        }
        
        return $this->reversedMapDef;
    }
}
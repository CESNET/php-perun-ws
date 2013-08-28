<?php

namespace PerunWs\Hydrator;

use Zend\Stdlib\Hydrator\HydratorInterface;
use InoPerunApi\Entity\GenericEntity;


class DefaultHydrator implements HydratorInterface
{


    public function hydrate(array $data, $object)
    {
        if (! $object instanceof GenericEntity) {
            throw new Exception\UnsupportedObjectException(get_class($object));
        }
    }


    public function extract($object)
    {
        /* @var $object \InoPerunApi\Entity\GenericEntity */
        if (! $object instanceof GenericEntity) {
            throw new Exception\UnsupportedObjectException(get_class($object));
        }
        
        $properties = $object->getProperties();
        
        return $properties;
    }
}
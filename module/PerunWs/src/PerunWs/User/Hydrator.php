<?php

namespace PerunWs\User;

use Zend\Stdlib\Hydrator\HydratorInterface;
use InoPerunApi\Entity\User;
use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use InoPerunApi\Entity\Collection\Collection;


class Hydrator implements HydratorInterface
{

    const FIELD_ID = 'id';

    const FIELD_FIRST_NAME = 'first_name';

    const FIELD_LAST_NAME = 'last_name';

    const FIELD_ORGANIZATION = 'organization';

    const FIELD_MAIL = 'mail';

    const FIELD_DISPLAY_NAME = 'display_name';

    protected $attributeMap = array(
        'organization' => self::FIELD_ORGANIZATION,
        'preferredMail' => self::FIELD_MAIL,
        'displayName' => self::FIELD_DISPLAY_NAME
    );


    public function hydrate(array $data, $user)
    {}


    public function extract($user)
    {
        if (! $user instanceof User) {
            throw new UnsupportedObjectException(get_class($user));
        }
        
        $data = array(
            self::FIELD_ID => $user->getId(),
            self::FIELD_FIRST_NAME => $user->getFirstName(),
            self::FIELD_LAST_NAME => $user->getLastName()
        );
        
        /* @var $attributes \InoPerunApi\Entity\Collection\Collection */
        $attributes = $user->getUserAttributes();

        if ($attributes instanceof Collection) {
            foreach ($attributes as $attribute) {
                /* @var $attribute \InoPerunApi\Entity\Attribute */
                if (isset($this->attributeMap[$attribute->getFriendlyName()])) {
                    $label = $this->attributeMap[$attribute->getFriendlyName()];
                    $value = $attribute->getValue();
                    $data[$label] = $value;
                }
            }
        }
        
        return $data;
    }
}
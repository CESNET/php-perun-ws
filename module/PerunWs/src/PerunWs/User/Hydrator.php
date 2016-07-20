<?php

namespace PerunWs\User;

use Zend\Stdlib\Hydrator\HydratorInterface;
use InoPerunApi\Entity\User;
use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use InoPerunApi\Entity\Collection\Collection;


/**
 * User hydrator.
 */
class Hydrator implements HydratorInterface
{

    const FIELD_ID = 'id';

    const FIELD_FIRST_NAME = 'first_name';

    const FIELD_LAST_NAME = 'last_name';

    const FIELD_ORGANIZATION = 'organization';

    const FIELD_MAIL = 'mail';

    const FIELD_PHONE = 'phone';

    const FIELD_DISPLAY_NAME = 'display_name';

    const FIELD_LANGUAGE = 'language';
    
    const FIELD_PRINCIPAL_NAMES = 'principal_names';
    
    const FIELD_TIMEZONE = 'timezone';

    /**
     * Maps Perun attribute names to local array field names.
     * 
     * @var array
     */
    protected $attributeMap = array(
        'organization' => self::FIELD_ORGANIZATION,
        'preferredMail' => self::FIELD_MAIL,
        'displayName' => self::FIELD_DISPLAY_NAME,
        'phone' => self::FIELD_PHONE,
        'preferredLanguage' => self::FIELD_LANGUAGE,
        'eduPersonPrincipalNames' => self::FIELD_PRINCIPAL_NAMES,
        'timezone' => self::FIELD_TIMEZONE
    );


    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $user)
    {}


    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
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
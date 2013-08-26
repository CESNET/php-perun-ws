<?php

namespace PerunWs\User;

use InoPerunApi\Manager\GenericManager;
use InoPerunApi\Entity\User;


class Storage implements StorageInterface
{

    /**
     * @var GenericManager
     */
    protected $manager;

    protected $attrMap = array(
        'preferredLanguage' => 'preferred_language',
        'address' => 'address',
        'organization' => 'organization',
        'preferredMail' => 'email',
        'phone' => 'phone',
        'workplace' => 'workplace',
        'displayName' => 'display_name'
    );


    public function __construct(GenericManager $manager)
    {
        $this->manager = $manager;
    }


    public function fetch($id)
    {
        $userEntity = $this->manager->getRichUserWithAttributes(array(
            'user' => $id
        ));
        $data = $this->entityToArray($userEntity);
        return $data;
    }


    public function fetchAll()
    {
        $collection = $this->manager->getUsers();
        foreach ($collection as $userEntity) {
            $data[] = $this->entityToArray($userEntity);
        }
        
        return $data;
    }


    protected function entityToArray(User $userEntity)
    {
        $data = array(
            'id' => $userEntity->getId(),
            'first_name' => $userEntity->getFirstName(),
            'last_name' => $userEntity->getLastName()
        );
        
        $attributes = $userEntity->getUserAttributes();
        foreach ($attributes as $attrEntity) {
            if (isset($this->attrMap[$attrEntity->getFriendlyName()])) {
                $label = $this->attrMap[$attrEntity->getFriendlyName()];
                $data[$label] = $attrEntity->getValue();
            }
        }
        return $data;
    }
}
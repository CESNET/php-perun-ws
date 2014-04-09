<?php

namespace PerunWs\Group\Service\Exception;


class GroupRetrievalException extends GroupGenericException
{

    /**
     * @var boolean
     */
    protected $notFound = false;


    /**
     * @return boolean
     */
    public function isNotFound()
    {
        return $this->notFound;
    }


    /**
     * @param boolean $notFound
     */
    public function setNotFound($notFound)
    {
        $this->notFound = (boolean) $notFound;
    }
}
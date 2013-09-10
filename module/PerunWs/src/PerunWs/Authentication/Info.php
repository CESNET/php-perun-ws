<?php

namespace PerunWs\Authentication;


class Info
{

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientDescription;


    /**
     * Constructor.
     * 
     * @param string $clientId
     * @param string $clientDescription
     */
    public function __construct($clientId, $clientDescription = '')
    {
        $this->setClientId($clientId);
        $this->setClientDescription($clientDescription);
    }


    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }


    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }


    /**
     * @return string
     */
    public function getClientDescription()
    {
        return $this->clientDescription;
    }


    /**
     * @param string $clientDescription
     */
    public function setClientDescription($clientDescription)
    {
        $this->clientDescription = $clientDescription;
    }
}
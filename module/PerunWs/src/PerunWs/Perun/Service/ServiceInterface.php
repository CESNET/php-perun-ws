<?php

namespace PerunWs\Perun\Service;

use Zend\Stdlib\Parameters;


interface ServiceInterface
{


    /**
     * @return Parameters
     */
    public function getParameters();


    /**
     * @param Parameters $parameters
     */
    public function setParameters(Parameters $parameters);
}
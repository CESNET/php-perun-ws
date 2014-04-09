<?php

namespace PerunWs\Util;

use Zend\Stdlib\Parameters;


class ParametersFactory
{


    public function createParameters(array $params = array())
    {
        return new Parameters($params);
    }
}
<?php

namespace PerunWs\Perun\Service\Exception;


class MissingParameterException extends \RuntimeException
{


    public function __construct($paramName)
    {
        parent::__construct(sprintf("Missing parameter '%s'", $paramName));
    }
}
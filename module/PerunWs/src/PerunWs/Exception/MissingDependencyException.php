<?php

namespace PerunWs\Exception;


class MissingDependencyException extends \RuntimeException
{


    public function __construct($dependencyName)
    {
        parent::__construct(sprintf("Missing dependency '%s'", $dependencyName));
    }
}
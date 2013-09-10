<?php

namespace PerunWs\Exception;


class UndefinedClassException extends \RuntimeException
{


    public function __construct($className)
    {
        parent::__construct(sprintf("Undefined class '%s'", $className));
    }
}
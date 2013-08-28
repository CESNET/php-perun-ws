<?php

namespace PerunWs\Hydrator\Exception;


class UnsupportedObjectException extends \InvalidArgumentException
{


    public function __construct($className)
    {
        parent::__construct(sprintf("Unsupported object '%s'", $className));
    }
}
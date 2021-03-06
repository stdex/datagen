<?php

declare(strict_types=1);

namespace Shapin\Datagen\Exception;

class UnknownProcessorException extends \Exception
{
    public function __construct($processor)
    {
        parent::__construct("Unkwnon processor \"$processor\".");
    }
}

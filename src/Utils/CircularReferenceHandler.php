<?php

namespace App\Utils;

class CircularReferenceHandler
{
    /**
     * @param $object
     *
     * @codeCoverageIgnore
     *
     * @return mixed
     */
    public function __invoke($object)
    {
        return $object->getId();
    }
}

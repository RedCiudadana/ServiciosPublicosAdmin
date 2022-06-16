<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ResourceEvent extends Event
{
    public const name = 'resource.event';

    protected $resource;

    public function __construct($resource) {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }
}

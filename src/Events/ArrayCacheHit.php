<?php

namespace Tmd\LaravelModelRepositories\Events;

class ArrayCacheHit
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}

<?php

namespace Tmd\LaravelRepositories\Events;

class ArrayCacheHit
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}

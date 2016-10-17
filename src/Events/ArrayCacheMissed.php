<?php

namespace Tmd\LaravelRepositories\Events;

class ArrayCacheMissed
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}

<?php

namespace Tmd\LaravelRepositories\Events;

class ArrayCacheWritten
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}

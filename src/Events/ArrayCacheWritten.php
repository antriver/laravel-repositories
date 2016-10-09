<?php

namespace Tmd\LaravelModelRepositories\Events;

class ArrayCacheWritten
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}

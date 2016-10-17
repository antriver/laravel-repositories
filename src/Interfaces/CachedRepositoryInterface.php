<?php

namespace Tmd\LaravelRepositories\Interfaces;

interface CachedRepositoryInterface
{
    /**
     * @param mixed $key
     */
    public function forget($key);

    /**
     * @param mixed $key
     */
    public function refresh($key);
}

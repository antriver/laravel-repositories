<?php

namespace Tmd\LaravelRepositories\Interfaces;

interface CachedRepositoryInterface
{
    /**
     * @param mixed $modelId
     */
    public function forgetById($modelId);

    /**
     * @param mixed $modelId
     */
    public function refreshById($modelId);
}

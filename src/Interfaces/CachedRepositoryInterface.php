<?php

namespace Tmd\LaravelRepositories\Interfaces;

interface CachedRepositoryInterface
{
    /**
     * @param int $modelId
     */
    public function forgetById(int $modelId);

    /**
     * @param int $modelId
     */
    public function refreshById(int $modelId);
}

<?php

namespace Antriver\LaravelRepositories\Tests\Repositories;

interface TestableCachedRepositoryInterface
{
    public function getCacheKeyPublic(int $modelId): string;

    public function getIdForFieldCacheKeyPublic(string $field, $value): string;
}

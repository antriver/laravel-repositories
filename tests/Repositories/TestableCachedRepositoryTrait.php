<?php

namespace Antriver\LaravelRepositories\Tests\Repositories;

trait TestableCachedRepositoryTrait
{
    public function getCacheKeyPublic(int $modelId): string
    {
        return $this->getCacheKey($modelId);
    }

    public function getIdForFieldCacheKeyPublic(string $field, $value): string
    {
        return $this->getIdForFieldCacheKey($field, $value);
    }
}

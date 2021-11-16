<?php

namespace Antriver\LaravelRepositories\Tests\Repositories;

use Antriver\LaravelRepositories\Base\AbstractCachedRepository;
use Antriver\LaravelRepositories\Tests\Models\Post;

class CachedPostRepository extends AbstractCachedRepository
{
    use TestableRepositoryTrait;
    use TestableCachedRepositoryTrait;

    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return Post::class;
    }
}

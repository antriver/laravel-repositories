<?php

namespace Tmd\LaravelRepositories\Tests\Repositories;

use Tmd\LaravelRepositories\Base\AbstractCachedRepository;
use Tmd\LaravelRepositories\Tests\Models\Post;

class CachedPostRepository extends AbstractCachedRepository
{
    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return string
     */
    public function getModelClass()
    {
        return Post::class;
    }
}

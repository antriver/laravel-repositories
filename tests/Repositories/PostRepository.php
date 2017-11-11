<?php

namespace Tmd\LaravelRepositories\Tests\Repositories;

use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Models\Post;

class PostRepository extends AbstractRepository
{
    use TestableRepositoryTrait;

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

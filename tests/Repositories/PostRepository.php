<?php

namespace Antriver\LaravelRepositories\Tests\Repositories;

use Antriver\LaravelRepositories\Base\AbstractRepository;
use Antriver\LaravelRepositories\Tests\Models\Post;

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

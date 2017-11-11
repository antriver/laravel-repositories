<?php

namespace Tmd\LaravelRepositories\Tests\Repositories;

use Tmd\LaravelRepositories\Base\AbstractCachedSoftDeletableRepository;
use Tmd\LaravelRepositories\Tests\Models\Comment;

class CachedCommentRepository extends AbstractCachedSoftDeletableRepository
{
    use TestableRepositoryTrait;

    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return string
     */
    public function getModelClass()
    {
        return Comment::class;
    }
}

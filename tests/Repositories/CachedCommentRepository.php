<?php

namespace Antriver\LaravelRepositories\Tests\Repositories;

use Antriver\LaravelRepositories\Base\AbstractCachedSoftDeletableRepository;
use Antriver\LaravelRepositories\Tests\Models\Comment;

class CachedCommentRepository extends AbstractCachedSoftDeletableRepository
{
    use TestableRepositoryTrait;
    use TestableCachedRepositoryTrait;

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

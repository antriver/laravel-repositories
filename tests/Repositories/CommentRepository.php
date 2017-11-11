<?php

namespace Tmd\LaravelRepositories\Tests\Repositories;

use Tmd\LaravelRepositories\Base\AbstractSoftDeletableRepository;
use Tmd\LaravelRepositories\Tests\Models\Comment;

class CommentRepository extends AbstractSoftDeletableRepository
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

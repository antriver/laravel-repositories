<?php

namespace Antriver\LaravelRepositories\Tests\Repositories;

use Antriver\LaravelRepositories\Base\AbstractSoftDeletableRepository;
use Antriver\LaravelRepositories\Tests\Models\Comment;

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

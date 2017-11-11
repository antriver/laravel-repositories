<?php

namespace Tmd\LaravelRepositories\Tests;

use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Repositories\CommentRepository;
use Tmd\LaravelRepositories\Tests\TestCases\SoftDeletableRepositoryTestsTrait;
use Tmd\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class AbstractSoftDeletableRepositoryTest extends RepositoryTestCase
{
    use TestsWithCommentsTrait;

    use StandardRepositoryTestsTrait;
    use SoftDeletableRepositoryTestsTrait;

    /**
     * @return AbstractRepository|CommentRepository
     */
    protected function createRepository()
    {
        return new CommentRepository();
    }
}

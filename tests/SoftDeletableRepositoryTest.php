<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Base\AbstractRepository;
use Antriver\LaravelRepositories\Tests\Repositories\CommentRepository;
use Antriver\LaravelRepositories\Tests\TestCases\SoftDeletableRepositoryTestsTrait;
use Antriver\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class SoftDeletableRepositoryTest extends AbstractRepositoryTestCase
{
    use SoftDeletableRepositoryTestsTrait;
    use StandardRepositoryTestsTrait;
    use TestsWithCommentsTrait;

    /**
     * @return AbstractRepository|CommentRepository
     */
    protected function createRepository()
    {
        return new CommentRepository();
    }
}

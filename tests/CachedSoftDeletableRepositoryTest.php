<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Base\AbstractRepository;
use Antriver\LaravelRepositories\Tests\Repositories\CachedCommentRepository;
use Antriver\LaravelRepositories\Tests\TestCases\CachedRepositoryTestsTrait;
use Antriver\LaravelRepositories\Tests\TestCases\CachedSoftDeletableRepositoryTestsTrait;
use Antriver\LaravelRepositories\Tests\TestCases\SoftDeletableRepositoryTestsTrait;
use Antriver\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class CachedSoftDeletableRepositoryTest extends AbstractRepositoryTestCase
{
    use TestsWithCommentsTrait;

    use StandardRepositoryTestsTrait;
    use CachedRepositoryTestsTrait;
    use SoftDeletableRepositoryTestsTrait;
    use CachedSoftDeletableRepositoryTestsTrait;

    /**
     * @return AbstractRepository
     */
    protected function createRepository()
    {
        return new CachedCommentRepository();
    }
}

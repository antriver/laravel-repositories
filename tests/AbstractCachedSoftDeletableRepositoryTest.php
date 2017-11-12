<?php

namespace Tmd\LaravelRepositories\Tests;

use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Repositories\CachedCommentRepository;
use Tmd\LaravelRepositories\Tests\TestCases\CachedRepositoryTestsTrait;
use Tmd\LaravelRepositories\Tests\TestCases\CachedSoftDeletableRepositoryTestsTrait;
use Tmd\LaravelRepositories\Tests\TestCases\SoftDeletableRepositoryTestsTrait;
use Tmd\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class AbstractCachedSoftDeletableRepositoryTest extends RepositoryTestCase
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

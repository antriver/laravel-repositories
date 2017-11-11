<?php

namespace Tmd\LaravelRepositories\Tests;

use Tmd\LaravelRepositories\Tests\Repositories\CachedPostRepository;
use Tmd\LaravelRepositories\Tests\TestCases\CachedRepositoryTestsTrait;
use Tmd\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class AbstractCachedRepositoryTest extends RepositoryTestCase
{
    use TestsWithPostsTrait;

    use StandardRepositoryTestsTrait;
    use CachedRepositoryTestsTrait;

    protected function createRepository()
    {
        return new CachedPostRepository();
    }
}

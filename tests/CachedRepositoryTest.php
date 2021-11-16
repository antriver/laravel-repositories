<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Tests\Repositories\CachedPostRepository;
use Antriver\LaravelRepositories\Tests\TestCases\CachedRepositoryTestsTrait;
use Antriver\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class CachedRepositoryTest extends AbstractRepositoryTestCase
{
    use CachedRepositoryTestsTrait;
    use StandardRepositoryTestsTrait;
    use TestsWithPostsTrait;

    protected function createRepository()
    {
        return new CachedPostRepository();
    }
}

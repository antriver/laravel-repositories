<?php

namespace Tmd\LaravelRepositories\Tests;

use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Repositories\PostRepository;
use Tmd\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class AbstractRepositoryTest extends RepositoryTestCase
{
    use TestsWithPostsTrait;

    use StandardRepositoryTestsTrait;

    /**
     * @return AbstractRepository
     */
    protected function createRepository()
    {
        return new PostRepository();
    }
}

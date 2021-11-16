<?php

namespace Antriver\LaravelRepositories\Tests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Antriver\LaravelRepositories\Base\AbstractRepository;
use Antriver\LaravelRepositories\Tests\Repositories\PostRepository;
use Antriver\LaravelRepositories\Tests\TestCases\StandardRepositoryTestsTrait;

class RepositoryTest extends AbstractRepositoryTestCase
{
    use StandardRepositoryTestsTrait;
    use TestsWithPostsTrait;

    /**
     * @return AbstractRepository
     */
    protected function createRepository()
    {
        return new PostRepository();
    }

    public function testCustomException()
    {
        PostRepository::setModelNotFoundExceptionFactory(
            function ($class, $field, $value) {
                $str = "{$class} {$field} {$value}";

                return new GoneHttpException($str);
            }
        );

        $this->expectException(GoneHttpException::class);
        $this->expectExceptionMessage("Antriver\\LaravelRepositories\\Tests\\Models\\Post text exception time");

        $repo = $this->createRepository();
        $repo->findOneByOrFail('text', 'exception time');

        PostRepository::setModelNotFoundExceptionFactory(null);
    }

    public function testCustomExceptionOnBase()
    {
        AbstractRepository::setModelNotFoundExceptionFactory(
            function ($class, $field, $value) {
                $str = "{$class} {$field} {$value}";

                return new GoneHttpException($str);
            }
        );

        $this->expectException(GoneHttpException::class);
        $this->expectExceptionMessage("Antriver\\LaravelRepositories\\Tests\\Models\\Post text exception time");

        $repo = $this->createRepository();
        $repo->findOneByOrFail('text', 'exception time');

        AbstractRepository::setModelNotFoundExceptionFactory(null);

        //$this->expectException(ModelNotFoundException::class);
        $repo->findOneByOrFail('text', 'exception time');
    }

    public function testUndoCustomException()
    {
        AbstractRepository::setModelNotFoundExceptionFactory(
            function ($class, $field, $value) {
                $str = "{$class} {$field} {$value}";

                return new GoneHttpException($str);
            }
        );

        AbstractRepository::setModelNotFoundExceptionFactory(null);

        $this->expectException(ModelNotFoundException::class);

        $repo = $this->createRepository();
        $repo->findOneByOrFail('text', 'exception time');
    }

    public function testGetModelClassWithoutNamespace()
    {
        $repo = $this->createRepository();
        $this->assertSame($repo->getModelClass(), 'Antriver\\LaravelRepositories\\Tests\\Models\\Post');
        $this->assertSame($repo->getModelClassWithoutNamespace(), 'Post');

        $this->assertSame($repo::removeNamespaceFromClass("Hello\\World"), 'World');
    }
}

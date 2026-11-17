<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Exceptions\CachedModelNotUnserializableException;
use Antriver\LaravelRepositories\Tests\Models\Comment;
use Antriver\LaravelRepositories\Tests\Models\Post;
use Antriver\LaravelRepositories\Tests\Repositories\CachedPostRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CachedSerializableClassesTest extends AbstractRepositoryTestCase
{
    use TestsWithPostsTrait;

    protected function createRepository()
    {
        return new CachedPostRepository();
    }

    /**
     * Sets the cache.serializable_classes option and recreates the repository so the cache store picks it up.
     * Skips the test on Laravel versions that ignore the option.
     *
     * @param array|bool $classes
     */
    private function useSerializableClasses($classes)
    {
        if (!method_exists(CacheManager::class, 'getSerializableClasses')) {
            $this->markTestSkipped('cache.serializable_classes needs Laravel 12+');
        }

        $this->app['config']->set('cache.serializable_classes', $classes);
        Cache::forgetDriver('file');
        $this->repository = $this->createRepository();
    }

    public function testFindThrowsWhenClassNotAllowed()
    {
        $this->useSerializableClasses(false);
        $this->repository->find(1);

        $this->expectException(CachedModelNotUnserializableException::class);
        $this->expectExceptionMessage(Post::class);
        $this->repository->find(1);
    }

    public function testFindOneByThrowsWhenClassNotAllowed()
    {
        $this->useSerializableClasses(false);
        $this->repository->findOneBy('text', 'Model 1');

        $this->expectException(CachedModelNotUnserializableException::class);
        $this->expectExceptionMessage(Post::class);
        $this->repository->findOneBy('text', 'Model 1');
    }

    public function testFindManyThrowsWhenClassNotAllowed()
    {
        $this->useSerializableClasses(false);
        $this->repository->find(1);

        $this->expectException(CachedModelNotUnserializableException::class);
        $this->expectExceptionMessage(Post::class);
        $this->repository->findMany([1, 2]);
    }

    public function testMissingModelStillReturnsNullWhenClassNotAllowed()
    {
        $this->useSerializableClasses(false);

        $this->assertNull($this->repository->find(999));
        $this->assertNull($this->repository->find(999));
        $this->assertNull($this->repository->findOneBy('text', 'nope'));
    }

    public function testFindWorksWhenClassAllowed()
    {
        $this->useSerializableClasses([Post::class]);
        $this->repository->find(1);

        $post = $this->repository->find(1);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSameModel($this->models[1], $post);
    }

    public function testRelationsWorkWithDocumentedClasses()
    {
        $this->useSerializableClasses([Post::class, Comment::class, Collection::class]);
        $post = $this->repository->find(1);
        $post->setRelation('comments', new Collection([new Comment(['text' => 'c'])]));
        $this->repository->rememberModel($post);

        $cached = $this->repository->find(1);

        $this->assertInstanceOf(Comment::class, $cached->comments->first());
    }
}

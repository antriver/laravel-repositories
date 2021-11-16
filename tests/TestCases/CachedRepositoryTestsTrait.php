<?php

namespace Antriver\LaravelRepositories\Tests\TestCases;

use Antriver\LaravelRepositories\Tests\Repositories\CachedPostRepository;
use Antriver\LaravelRepositories\Tests\Repositories\TestableCachedRepositoryInterface;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Antriver\LaravelRepositories\Base\AbstractCachedRepository;

trait CachedRepositoryTestsTrait
{
    /**
     * @var Store
     */
    protected $cache;

    /**
     * @var Model[]
     */
    protected $models;

    /**
     * @var AbstractCachedRepository|TestableCachedRepositoryInterface
     */
    protected $repository;

    public function testFindStoresInCache()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':1'));

        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->find(1)
        );

        $cachedModel = $this->cache->get($this->getModelNameString().':1');
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $cachedModel
        );

        $this->assertSame('Model 1', $cachedModel->text);
    }

    public function testFindStoresFalseInCache()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':456'));

        // Ensure 'false' is cached to remember that a post does not exist.
        $this->assertNull($this->repository->find(456));

        $this->assertFalse($this->cache->get($this->getModelNameString().':456'));
    }

    public function testFindUsesCache()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':123'));

        $post = $this->getTestModelInstance(['id' => 123, 'text' => 'Cached Hello World']);
        $this->cache->forever($this->getModelNameString().':123', $post);

        $returnedPost = $this->repository->find(123);

        $this->assertInstanceOf($this->getTestModelClass(), $returnedPost);
        $this->assertSame('Cached Hello World', $returnedPost->text);
    }

    public function testCachedFalseIsUsed()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':2'));

        // Pretend that model 2 (which was created in setUp()) was looked for and not found.
        $this->cache->forever($this->getModelNameString().':2', false);

        // Ensure it really exists in the database.
        $this->assertInstanceOf($this->getTestModelClass(), $this->find(2));

        // Test the repository thinks it does not exist due to the cached false.
        $this->assertNull($this->repository->find(2));
        $this->assertNull($this->repository->find(2));

        // Clear the cache and it should be returned now
        $this->cache->forget($this->getModelNameString().':2');
        $this->assertInstanceOf($this->getTestModelClass(), $this->repository->find(2));
    }

    public function testRemoveRemovesFromCache()
    {
        $post = $this->getTestModelInstance(['text' => 'Hello World']);

        $this->repository->persist($post);

        $postId = $post->id;

        $this->assertInstanceOf(
            $this->repository->getModelClass(),
            $this->cache->get($this->getModelNameString().':'.$postId)
        );

        $this->repository->remove($post);

        $this->assertNull($this->cache->get($this->getModelNameString().':'.$postId));

        // It is up to the individual repositories to remove cached field/Id caches in their onDelete()
        // method so we don't test that!
    }

    public function testFindOneByInsertsToCache()
    {
        $cacheKey = $this->repository->getIdForFieldCacheKeyPublic('text', 'Model 2');
        $this->assertNull($this->cache->get($cacheKey));

        // Find a model with the text "Model 2".
        $this->assertInstanceOf(
            $this->repository->getModelClass(),
            $this->repository->findOneBy(
                'text',
                'Model 2'
            )
        );

        // The id should now exist in the cache.
        $this->assertSame(
            2,
            $this->cache->get($cacheKey)
        );
    }

    public function testFindOneByUsesCache()
    {
        $cacheKey = $this->repository->getIdForFieldCacheKeyPublic('text', 'Model 2');
        $this->assertNull($this->cache->get($cacheKey));

        // Pretend we found an ID for this value previously, but it's one that doesn't exist:
        $this->cache->forever($cacheKey, 400);

        // Ensure it really exists in the database.
        $existingPost = $this->find(2);
        $this->assertInstanceOf($this->getTestModelClass(), $existingPost);
        $this->assertSame('Model 2', $existingPost->text);

        $this->assertNull($this->repository->findOneBy('text', 'Model 2'));
    }

    public function testFindManyStoresInCache()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':1'));
        $this->assertNull($this->cache->get($this->getModelNameString().':2'));
        $this->assertNull($this->cache->get($this->getModelNameString().':500'));

        $result = $this->repository->findMany([1, 2, 500]);

        $actual = array_map(
            function ($result) {
                return $result->id;
            },
            $result->all()
        );
        sort($actual);

        $this->assertSame([1, 2], $actual);

        $this->assertInstanceOf($this->getTestModelClass(), $this->cache->get($this->getModelNameString().':1'));
        $this->assertInstanceOf($this->getTestModelClass(), $this->cache->get($this->getModelNameString().':2'));
        $this->assertFalse($this->cache->get($this->getModelNameString().':500'));
    }

    public function testFindManyUsesCache()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':1'));
        $this->assertNull($this->cache->get($this->getModelNameString().':2'));
        $this->assertNull($this->cache->get($this->getModelNameString().':500'));

        // Insert a cached ID of 500 so it should come out in the result
        $model = $this->getTestModelInstance([
            'id' => 500,
            'name' => 'Model 500'
        ]);
        $this->cache->forever($this->getModelNameString().':500', $model);

        $result = $this->repository->findMany([1, 2, 500]);

        $actual = array_map(
            function ($result) {
                return $result->id;
            },
            $result->all()
        );
        sort($actual);

        $this->assertSame([1, 2, 500], $actual);

        $this->assertInstanceOf($this->getTestModelClass(), $this->cache->get($this->getModelNameString().':1'));
        $this->assertInstanceOf($this->getTestModelClass(), $this->cache->get($this->getModelNameString().':2'));
        $this->assertInstanceOf($this->getTestModelClass(), $this->cache->get($this->getModelNameString().':500'));
    }

    public function testFindManyUsesCachedFalse()
    {
        // Insert a cached false for ID 2 so it should be excluded from the result and not query the DB for it.
        $this->cache->forever($this->getModelNameString().':1', false);

        $result = $this->repository->findMany([1, 2, 500]);

        $actual = array_map(
            function ($result) {
                return $result->id;
            },
            $result->all()
        );
        sort($actual);

        $this->assertSame([2], $actual);
    }
}

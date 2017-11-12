<?php

namespace Tmd\LaravelRepositories\Tests\TestCases;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRepositories\Base\AbstractCachedRepository;

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
     * @var AbstractCachedRepository
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
        $cacheKey = $this->getModelNameString().'-text-id:model-2';
        $this->assertNull($this->cache->get($cacheKey));

        $this->assertInstanceOf(
            $this->repository->getModelClass(),
            $this->repository->findOneBy(
                'text',
                'Model 2'
            )
        );

        $this->assertSame(
            2,
            $this->cache->get($cacheKey)
        );
    }

    public function testFindOneByUsesCache()
    {
        $cacheKey = $this->getModelNameString().'-text-id:model-2';
        $this->assertNull($this->cache->get($cacheKey));

        // Pretend we found an ID for this value previously, but it's one that doesn't exist:
        $this->cache->forever($cacheKey, 400);

        // Ensure it really exists in the database.
        $existingPost = $this->find(2);
        $this->assertInstanceOf($this->getTestModelClass(), $existingPost);
        $this->assertSame('Model 2', $existingPost->text);

        $this->assertNull($this->repository->findOneBy('text', 'Model 2'));
    }
}

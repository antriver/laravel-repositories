<?php

namespace Antriver\LaravelRepositories\Tests\TestCases;

use Antriver\LaravelRepositories\Tests\Repositories\TestableCachedRepositoryInterface;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Antriver\LaravelRepositories\Base\AbstractCachedRepository;
use Antriver\LaravelRepositories\Base\AbstractCachedSoftDeletableRepository;

trait CachedSoftDeletableRepositoryTestsTrait
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
     * @var AbstractCachedRepository|AbstractCachedSoftDeletableRepository|TestableCachedRepositoryInterface
     */
    protected $repository;

    public function testFindReturnsNullForCachedSoftDeletedModel()
    {
        // The CachedRepositoryTestsTrait will already establish that it returns cached items at all.
        // Here we are checking it does not return a model if it is cached but soft deleted.

        $this->assertNull($this->cache->get($this->getModelNameString().':555'));

        $model = $this->getTestModelInstance(
            [
                'id' => 555,
                'text' => 'Cached Deleted',
                'deleted_at' => '2000-01-01 00:00:00',
            ]
        );
        $this->cache->forever($this->getModelNameString().':555', $model);

        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->cache->get($this->getModelNameString().':555')
        );

        $this->assertNull($this->repository->find(555));

        // Test it is returned when it should be
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findWithTrashed(555)
        );

        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findTrashed(555)
        );

        // And not returned when it should not be
        $this->assertNull($this->repository->find(555));
    }

    public function testFindStoresSoftDeletedModelInCache()
    {
        $this->assertNull(
            $this->cache->get($this->getModelNameString().':3')
        );

        $this->assertNull($this->repository->find(3));

        // The model will have been loaded and cached even though it is soft deleted.
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->cache->get($this->getModelNameString().':3')
        );

        // Try getting it again and ensure it's still not returned.
        $this->assertNull($this->repository->find(3));
    }

    public function testFindOneByReturnsNullForCachedSoftDeletedModel()
    {
        // The CachedRepositoryTestsTrait will already establish that it returns cached items at all.
        // Here we are checking it does not return a model if it is cached but soft deleted.

        $cacheKey = $this->repository->getCacheKeyPublic(666);
        $this->assertNull($this->cache->get($cacheKey));

        $model = $this->getTestModelInstance(
            [
                'id' => 666,
                'text' => 'Cached Deleted Hello World',
                'deleted_at' => '2000-01-01 00:00:00',
            ]
        );

        // Cache the ID for the field value
        $cacheKeyForIdByName = $this->repository->getIdForFieldCacheKeyPublic('text', 'Cached Deleted Hello World');
        $this->cache->forever($cacheKeyForIdByName, 666);

        // Cache the model too
        $this->cache->forever($cacheKey, $model);

        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->cache->get($cacheKey)
        );

        $this->assertNull(
            $this->repository->findOneBy('text', 'Cached Deleted Hello World')
        );

        // Test it is returned when it should be
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findOneByWithTrashed('text', 'Cached Deleted Hello World')
        );

        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findTrashedOneBy('text', 'Cached Deleted Hello World')
        );

        // And not returned when it should not be
        $this->assertNull(
            $this->repository->findOneBy('text', 'Cached Deleted Hello World')
        );
    }

    public function testFindOneByStoresSoftDeletedModelInCache()
    {
        $this->assertNull(
            $this->cache->get($this->getModelNameString().':3')
        );

        $this->assertNull($this->repository->findOneBy('text', 'Model 3'));

        // The model will have been loaded and cached even though it is soft deleted.
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->cache->get($this->getModelNameString().':3')
        );

        // Try getting it again and ensure it's still not returned.
        $this->assertNull($this->repository->findOneBy('text', 'Model 3'));
    }
}

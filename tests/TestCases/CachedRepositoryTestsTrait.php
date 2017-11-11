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

    public function testFindUsesCache()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':123'));

        $post = $this->getTestModelInstance(['id' => 123, 'text' => 'Cached Hello World']);
        $this->cache->forever($this->getModelNameString().':123', $post);

        $returnedPost = $this->repository->find(123);

        $this->assertInstanceOf($this->getTestModelClass(), $returnedPost);
        $this->assertSame('Cached Hello World', $returnedPost->text);
    }

    public function testFindStoresFalseInCache()
    {
        // Ensure 'false' is cached to remember that a post does not exist.
        $this->repository->find(456);

        $this->assertFalse($this->cache->get($this->getModelNameString().':456'));
    }

    public function testCachedFalseIsUsed()
    {
        $this->assertNull($this->cache->get($this->getModelNameString().':789'));

        // Pretend this post was looked for and not found.
        $this->cache->forever($this->getModelNameString().':789', false);

        // Make the post actually exist
        $post = $this->getTestModelInstance(
            [
                'id' => 789,
                'text' => 'Hello world',
            ]
        );
        $post->save();

        // Ensure it exists
        $this->assertInstanceOf($this->getTestModelClass(), $this->find(789));

        // Test the repository thinks it does not exist due to the cached false.
        $this->assertNull($this->repository->find(789));
        $this->assertNull($this->repository->find(789));
        $this->cache->forget($this->getModelNameString().':789');
        $this->assertInstanceOf($this->getTestModelClass(), $this->repository->find(789));
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
    }

    public function testGetCacheKey()
    {

        //$key = $repository->getCacheKey(1);
    }
}

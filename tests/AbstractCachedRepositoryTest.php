<?php

namespace Tmd\LaravelRepositories\Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\FileStore;
use Illuminate\Contracts\Cache\Store;
use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Models\Post;
use Tmd\LaravelRepositories\Tests\Repositories\CachedPostRepository;

class AbstractCachedRepositoryTest extends AbstractRepositoryTest
{
    /**
     * @var Store
     */
    protected $cache;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('cache.default', 'file');
        $app['config']->set(
            'cache.stores.file',
            [
                'driver' => 'file',
                'path' => __DIR__.'/cache'
            ]
        );

        $cacheManager = new CacheManager($app);
        $this->cache = $cacheManager->store('file');
    }

    public function setUp()
    {
        parent::setUp();

        $this->cache->flush();
    }

    protected function getRepository()
    {
        return new CachedPostRepository();
    }

    public function testFindUsesCache()
    {
        $this->assertNull($this->cache->get('post:123'));

        $post = new Post(['id' => 123, 'text' => 'Cached Hello World']);
        $this->cache->forever('post:123', $post);

        $repository = $this->getRepository();

        $returnedPost = $repository->find(123);

        $this->assertInstanceOf($repository->getModelClass(), $returnedPost);
        $this->assertSame('Cached Hello World', $returnedPost->text);
    }

    public function testFindStoresFalseInCache()
    {
        $repository = $this->getRepository();

        $repository->find(789);

        $this->assertFalse($this->cache->get('post:789'));
    }

    public function testCachedFalseIsUsed()
    {
        $repository = $this->getRepository();

        $repository->find(789);

        $this->assertFalse($this->cache->get('post:789'));
    }

    public function testFindReturnsNullForEmptyKey()
    {
        $repository = $this->getRepository();

        $this->assertNull($repository->find(''));
    }

    public function testRemoveRemovesFromCache()
    {
        $repository = $this->getRepository();
        $post = new Post(['text' => 'Hello World']);

        $repository->persist($post);

        $postId = $post->id;

        $this->assertInstanceOf($repository->getModelClass(), $this->cache->get('post:'.$postId));

        $repository->remove($post);

        $this->assertNull($this->cache->get('post:'.$postId));
    }



    public function testGetCacheKey()
    {
        $repository = $this->getRepository();

        //$key = $repository->getCacheKey(1);
    }
}

<?php

namespace Antriver\LaravelRepositories\Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\FileStore;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase;
use Antriver\LaravelRepositories\Base\AbstractRepository;

abstract class AbstractRepositoryTestCase extends TestCase
{
    /**
     * @var FileStore
     */
    protected $cache;

    /**
     * @var Model[]
     */
    protected $models;

    /**
     * @var AbstractRepository
     */
    protected $repository;

    /**
     * @return AbstractRepository
     */
    abstract protected function createRepository();

    /**
     * @return string
     */
    abstract protected function getTestModelClass(): string;

    /**
     * @return string
     */
    abstract protected function getModelNameString(): string;

    /**
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function getTestModelInstance(array $attributes = []);

    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function find($id)
    {
        return ($this->getTestModelClass())::find($id);
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
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
                'path' => __DIR__.'/cache',
            ]
        );

        $cacheManager = new CacheManager($app);
        $this->cache = $cacheManager->store();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->models = [];

        // Empty cache before every test
        $this->cache->flush();

        $this->repository = $this->createRepository();
    }

    public function tearDown(): void
    {
        // Empty cache after every test
        $this->cache->flush();

        $this->models = [];

        $this->beforeApplicationDestroyed(function () {
            \DB::disconnect();
        });

        parent::tearDown();
    }

    /**
     * Instead of comparing the whole models, compare some attributes we know should be the same.
     *
     * @param $expected
     * @param $actual
     */
    protected function assertSameModel($expected, $actual)
    {
        if (!empty($expected->id)) {
            $this->assertSame($expected->id, $actual->id);
        }
        $this->assertSame($expected->text, $actual->text);
    }
}

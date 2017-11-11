<?php

namespace Tmd\LaravelRepositories\Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\FileStore;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase;
use Tmd\LaravelRepositories\Base\AbstractRepository;

abstract class RepositoryTestCase extends TestCase
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

        $app['config']->set('database.default', 'laravel-repositories-tests');
        $app['config']->set(
            'database.connections.laravel-repositories-tests',
            [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'laravel-repositories-tests',
                'username' => 'root',
                'password' => 'root',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ]
        );

        $app['config']->set('cache.default', 'file');
        $app['config']->set(
            'cache.stores.file',
            [
                'driver' => 'file',
                'path' => __DIR__.'/cache',
            ]
        );

        $cacheManager = new CacheManager($app);
        $this->cache = $cacheManager->store('file');
    }

    public function setUp()
    {
        parent::setUp();

        $this->models = [];

        // Empty cache before every test
        $this->cache->flush();

        $this->repository = $this->createRepository();
    }

    public function tearDown()
    {
        // Empty cache after every test
        $this->cache->flush();

        $this->models = [];

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

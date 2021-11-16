<?php

namespace Antriver\LaravelRepositories\Tests\TestCases;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Antriver\LaravelRepositories\Base\AbstractCachedSoftDeletableRepository;
use Antriver\LaravelRepositories\Base\AbstractSoftDeletableRepository;

trait SoftDeletableRepositoryTestsTrait
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
     * @var AbstractSoftDeletableRepository|AbstractCachedSoftDeletableRepository
     */
    protected $repository;

    /**
     * Ensure the test soft deleted model exists in the database.
     */
    public function testSoftDeletedInDb()
    {
        $this->assertNull($this->find($this->models[3]->id));

        $instance = $this->getTestModelInstance();
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $instance->newQuery()->withTrashed()->find($this->models[3]->id)
        );
    }

    public function testFindDoesNotReturnSoftDeleted()
    {
        $this->assertNull($this->repository->find(3));
    }

    public function testFindOneByDoesNotReturnSoftDeleted()
    {
        $this->assertNull($this->repository->findOneBy('text', 'Model 3'));
    }

    /**
     * Find models that MAY be trashed by primary key.
     */

    public function testFindWithTrashedReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findWithTrashed(3)
        );
    }

    public function testFindWithTrashedForInvalidModelReturnsNull()
    {
        $this->assertNull(
            $this->repository->findWithTrashed(100)
        );
    }

    public function testFindWithTrashedForLiveModelReturnsNull()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findWithTrashed(1)
        );
    }

    public function testFindWithTrashedOrFailReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findWithTrashedOrFail(3)
        );
    }

    public function testFindWithTrashedReturnsLiveModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findWithTrashedOrFail(1)
        );
    }

    public function testFindWithTrashedOrFailThrowsExceptionForInvalidModel()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findWithTrashedOrFail(500);
    }

    /**
     * Find models that MUST be trashed by primary key.
     */

    public function testFindTrashedReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findTrashed(3)
        );
    }

    public function testFindTrashedForInvalidModelReturnsNull()
    {
        $this->assertNull(
            $this->repository->findTrashed(100)
        );
    }

    public function testFindTrashedForLiveModelReturnsNull()
    {
        $this->assertNull(
            $this->repository->findTrashed(1)
        );
    }

    public function testFindTrashedOrFailReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findTrashedOrFail(3)
        );
    }

    public function testFindTrashedOrFailThrowsExceptionForLiveModel()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findTrashedOrFail(1);
    }

    public function testFindTrashedOrFailThrowsExceptionForInvalidModel()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findTrashedOrFail(500);
    }

    /**
     * Find models that MAY be trashed by specified field.
     */

    public function testFindOneByWithTrashedReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findOneByWithTrashed('text', 'Model 3')
        );
    }

    public function testFindOneByWithTrashedReturnsNullForInvalidModel()
    {
        $this->assertNull(
            $this->repository->findOneByWithTrashed('text', 'Model 100')
        );
    }

    public function testFindOneByWithTrashedReturnsLiveModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findOneByWithTrashed('text', 'Model 1')
        );
    }

    public function testFindOneByWithTrashedOrFailReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findOneByWithTrashedOrFail('text', 'Model 3')
        );
    }

    public function testFindOneByWithTrashedOrFailThrowsExceptionForInvalidModel()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findOneByWithTrashedOrFail('text', 'Model 100');
    }

    public function testFindOneByWithTrashedOrFailReturnsLiveModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findOneByWithTrashedOrFail('text', 'Model 1')
        );
    }

    /**
     * Find models that MUST be trashed by specified field.
     */

    public function testFindTrashedOneByReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findTrashedOneBy('text', 'Model 3')
        );
    }

    public function testFindTrashedOneByReturnsNullForInvalidModel()
    {
        $this->assertNull(
            $this->repository->findTrashedOneBy('text', 'Model 100')
        );
    }

    public function testFindTrashedOneByReturnsNullForLiveModel()
    {
        $this->assertNull(
            $this->repository->findTrashedOneBy('text', 'Model 1')
        );
    }

    public function testFindTrashedOneByOrFailReturnsModel()
    {
        $this->assertInstanceOf(
            $this->getTestModelClass(),
            $this->repository->findTrashedOneByOrFail('text', 'Model 3')
        );
    }

    public function testFindTrashedOneByOrFailForInvalidModelThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findTrashedOneByOrFail('text', 'Model 100');
    }

    public function testFindTrashedOneByOrFailForLiveModelThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findTrashedOneByOrFail('text', 'Model 1');
    }
}

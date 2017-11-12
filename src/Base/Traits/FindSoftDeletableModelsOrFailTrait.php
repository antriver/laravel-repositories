<?php

namespace Tmd\LaravelRepositories\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Interfaces\SoftDeletableRepositoryInterface;

/**
 * Implementation of some of the methods in SoftDeletableRepositoryInterface.
 */
trait FindSoftDeletableModelsOrFailTrait
{
    /**
     * Return a model by its primary key that MAY be soft deleted or throw an exception if not found.
     *
     * @param mixed $modelId
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findWithTrashedOrFail($modelId)
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findWithTrashed($modelId)) {
            return $model;
        }

        throw $this->createNotFoundException($modelId);
    }

    /**
     * Return a model by its primary key that MUST be soft deleted or throw an exception if not found.
     *
     * @param mixed $modelId
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findTrashedOrFail($modelId)
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findTrashed($modelId)) {
            return $model;
        }

        throw $this->createNotFoundException($modelId);
    }

    /**
     * Return a model by matching the specified field that MAY be soft deleted or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOneByWithTrashedOrFail(string $field, $value)
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findOneByWithTrashed($field, $value)) {
            return $model;
        }

        throw $this->createNotFoundException($value, $field);
    }

    /**
     * Return a model by matching the specified field that MUST be soft deleted or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findTrashedOneByOrFail(string $field, $value)
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findTrashedOneBy($field, $value)) {
            return $model;
        }

        throw $this->createNotFoundException($value, $field);
    }
}

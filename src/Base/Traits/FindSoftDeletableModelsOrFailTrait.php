<?php

namespace Tmd\LaravelRepositories\Base\Traits;

use Illuminate\Database\Eloquent\Model;
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
     */
    public function findWithTrashedOrFail($modelId): Model
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
     */
    public function findTrashedOrFail($modelId): Model
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
     */
    public function findOneByWithTrashedOrFail($field, $value): Model
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
     */
    public function findTrashedOneByOrFail($field, $value): Model
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findTrashedOneBy($field, $value)) {
            return $model;
        }

        throw $this->createNotFoundException($value, $field);
    }
}

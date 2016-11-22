<?php

namespace Tmd\LaravelRepositories\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Interfaces\SoftDeletableRepositoryInterface;

trait SoftDeletableOrFailTrait
{
    /**
     * Return a model that MAY be soft deleted or throw an Exception.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findWithTrashedOrFail($key)
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findWithTrashed($key)) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel($this->getModelClass(), $key);
    }

    /**
     * Return a model that MUST be soft deleted or throw an Exception.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findTrashedOrFail($key)
    {
        /** @var AbstractRepository|SoftDeletableRepositoryInterface|self $this */

        if ($model = $this->findTrashed($key)) {
            return $model;
        }

        /** @var AbstractRepository|self $this */
        throw (new ModelNotFoundException)->setModel($this->getModelClass(), $key);
    }
}

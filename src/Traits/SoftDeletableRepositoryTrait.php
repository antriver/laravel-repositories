<?php

namespace Tmd\LaravelRepositories\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Implementation of SoftDeletableRepositoryInterface
 */
trait SoftDeletableRepositoryTrait
{
    /**
     * Return a model that MAY be soft deleted.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findWithTrashed($key)
    {
        $class = $this->getModelClass();

        return $class::withTrashed()->find($key);
    }

    /**
     * Return a model that MAY be soft deleted or throw an Exception.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findWithTrashedOrFail($key)
    {
        $class = $this->getModelClass();

        return $class::withTrashed()->findOrFail($key);
    }

    /**
     * Return a model that MUST be soft deleted.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findTrashed($key)
    {
        $class = $this->getModelClass();

        return $class::onlyTrashed()->find($key);
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
        $class = $this->getModelClass();

        return $class::onlyTrashed()->findOrFail($key);
    }
}

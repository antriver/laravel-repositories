<?php

namespace Tmd\LaravelRepositories\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRepositories\Base\AbstractRepository;

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
        /** @var AbstractRepository $this */
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
        /** @var AbstractRepository $this */
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
        /** @var AbstractRepository $this */
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
        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::onlyTrashed()->findOrFail($key);
    }
}

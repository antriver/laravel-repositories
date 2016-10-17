<?php

namespace Tmd\LaravelRepositories\Interfaces;

use Illuminate\Database\Eloquent\Model as EloquentModel;

interface SoftDeletableRepositoryInterface
{
    /**
     * Return a model that MAY be soft deleted.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findWithTrashed($key);

    /**
     * Return a model that MAY be soft deleted or throw an Exception.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findWithTrashedOrFail($key);

    /**
     * Return a model that MUST be soft deleted.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findTrashed($key);

    /**
     * Return a model that MUST be soft deleted or throw an Exception.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findTrashedOrFail($key);
}

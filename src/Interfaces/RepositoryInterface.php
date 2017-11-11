<?php

namespace Tmd\LaravelRepositories\Interfaces;

use Illuminate\Database\Eloquent\Model as EloquentModel;

interface RepositoryInterface
{
    /**
     * Return a model by its primary key.
     *
     * @param mixed $modelId
     *
     * @return EloquentModel|null
     */
    public function find($modelId);

    /**
     * Return a model by its primary key or throw an exception if not found.
     *
     * @param mixed $modelId
     *
     * @return EloquentModel|null
     */
    public function findOrFail($modelId);

    /**
     * Return a model by matching the specified field.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return EloquentModel|null
     */
    public function findOneBy($field, $value);

    /**
     * Return a model by matching the specified field or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return EloquentModel|null
     */
    public function findOneByOrFail($field, $value);

    /**
     * Save a model to the database.
     *
     * @param EloquentModel $model
     *
     * @return EloquentModel
     */
    public function persist(EloquentModel $model);

    /**
     * Delete a model from the database.
     *
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function remove(EloquentModel $model);
}

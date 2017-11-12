<?php

namespace Tmd\LaravelRepositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Return a model by its primary key.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function find($modelId);

    /**
     * Return a model by its primary key or throw an exception if not found.
     *
     * @param mixed $modelId
     *
     * @return Model
     */
    public function findOrFail($modelId);

    /**
     * Return a model by matching the specified field.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findOneBy(string $field, $value);

    /**
     * Return a model by matching the specified field or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     */
    public function findOneByOrFail(string $field, $value);

    /**
     * Save a model to the database.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function persist(Model $model);

    /**
     * Delete a model from the database.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function remove(Model $model);
}

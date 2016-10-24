<?php

namespace Tmd\LaravelRepositories\Interfaces;

use Illuminate\Database\Eloquent\Model as EloquentModel;

interface RepositoryInterface
{
    /**
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function find($key);

    /**
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findOrFail($key);

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findOneBy($field, $value);

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findOneByOrFail($field, $value);

    /**
     * @param EloquentModel $model
     *
     * @return EloquentModel
     */
    public function persist(EloquentModel $model);
}

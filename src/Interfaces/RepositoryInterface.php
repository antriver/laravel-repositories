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
    public function findBy($field, $value);

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findByOrFail($field, $value);
}

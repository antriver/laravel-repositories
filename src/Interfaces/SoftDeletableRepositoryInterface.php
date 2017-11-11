<?php

namespace Tmd\LaravelRepositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface SoftDeletableRepositoryInterface
{
    /**
     * Return a model by its primary key that MAY be soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function findWithTrashed($modelId);

    /**
     * Return a model by its primary key that MAY be soft deleted or throw an exception if not found.
     *
     * @param mixed $modelId
     *
     * @return Model
     */
    public function findWithTrashedOrFail($modelId): Model;

    /**
     * Return a model by its primary key that MUST be soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function findTrashed($modelId);

    /**
     * Return a model by its primary key that MUST be soft deleted or throw an exception if not found.
     *
     * @param mixed $modelId
     *
     * @return Model
     */
    public function findTrashedOrFail($modelId): Model;

    /**
     * Return a model by matching the specified field that MAY be soft deleted.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findOneByWithTrashed($field, $value);

    /**
     * Return a model by matching the specified field that MAY be soft deleted or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     */
    public function findOneByWithTrashedOrFail($field, $value): Model;

    /**
     * Return a model by matching the specified field that MUST be soft deleted.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findTrashedOneBy($field, $value);

    /**
     * Return a model by matching the specified field that MUST be soft deleted or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     */
    public function findTrashedOneByOrFail($field, $value): Model;
}

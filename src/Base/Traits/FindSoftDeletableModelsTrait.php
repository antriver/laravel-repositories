<?php

namespace Tmd\LaravelRepositories\Base\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRepositories\Base\AbstractRepository;

/**
 * Implementation of some of the methods in SoftDeletableRepositoryInterface by always querying the database.
 */
trait FindSoftDeletableModelsTrait
{
    /**
     * Return a model by its primary key that MAY be soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function findWithTrashed($modelId)
    {
        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::withTrashed()->find($modelId);
    }

    /**
     * Return a model by its primary key that MUST be soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function findTrashed($modelId)
    {
        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::onlyTrashed()->find($modelId);
    }

    /**
     * Return a model by matching the specified field that MAY be soft deleted.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     * @throws Exception
     */
    public function findOneByWithTrashed($field, $value)
    {
        if (empty($field)) {
            throw new Exception("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::withTrashed()->where($field, $value)->first();
    }

    /**
     * Return a model by matching the specified field that MUST be soft deleted.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     * @throws Exception
     */
    public function findTrashedOneBy($field, $value)
    {
        if (empty($field)) {
            throw new Exception("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::onlyTrashed()->where($field, $value)->first();
    }
}

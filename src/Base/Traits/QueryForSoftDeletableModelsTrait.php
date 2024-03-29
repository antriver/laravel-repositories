<?php

namespace Antriver\LaravelRepositories\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Antriver\LaravelRepositories\Base\AbstractRepository;

/**
 * Implementation of some of the methods in SoftDeletableRepositoryInterface by always querying the database.
 */
trait QueryForSoftDeletableModelsTrait
{
    /**
     * Return a model by its primary key that MAY be soft deleted.
     *
     * @param int $modelId
     *
     * @return Model|null
     */
    public function findWithTrashed(int $modelId)
    {
        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::withTrashed()->find($modelId);
    }

    /**
     * Return a model by its primary key that MUST be soft deleted.
     *
     * @param int $modelId
     *
     * @return Model|null
     */
    public function findTrashed(int $modelId)
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
     * @throws InvalidArgumentException
     */
    public function findOneByWithTrashed(string $field, $value)
    {
        if (empty($field)) {
            throw new InvalidArgumentException("A field must be specified.");
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
     * @throws InvalidArgumentException
     */
    public function findTrashedOneBy(string $field, $value)
    {
        if (empty($field)) {
            throw new InvalidArgumentException("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::onlyTrashed()->where($field, $value)->first();
    }
}

<?php

namespace Tmd\LaravelRepositories\Base;

use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRepositories\Base\Traits\FindSoftDeletableModelsOrFailTrait;
use Tmd\LaravelRepositories\Base\Traits\QueryForSoftDeletableModelsTrait;
use Tmd\LaravelRepositories\Interfaces\SoftDeletableRepositoryInterface;

abstract class AbstractCachedSoftDeletableRepository extends AbstractCachedRepository implements SoftDeletableRepositoryInterface
{
    use QueryForSoftDeletableModelsTrait;
    use FindSoftDeletableModelsOrFailTrait;

    /**
     * Return a model by its primary key.
     *
     * If the model is not found boolean 'false' is cached to remember that it does not exist.
     * (Thee method will always return a model or null regardless)
     *
     * The model will be loaded and cached even if it is soft deleted!
     * Models will only be returned if it is not soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function find($modelId): ?Model
    {
        // Because of the overridden queryDatabaseForModelByKey, the findModelById() method will
        // return all models including soft deleted ones.

        // Return that result as long as it is NOT soft deleted.
        if ($model = $this->findModelById($modelId)) {
            return $model->trashed() ? null : $model;
        }

        return null;
    }

    /**
     * Return a model by its primary key that MAY be soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function findWithTrashed($modelId): ?Model
    {
        // Because of the overridden queryDatabaseForModelByKey, the findModelById() method will
        // return all models including soft deleted ones.

        // Return that result, because we want both soft deleted and not soft deleted here.
        return $this->findModelById($modelId);
    }

    /**
     * Return a model by its primary key that MUST be soft deleted.
     *
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function findTrashed($modelId): ?Model
    {
        // Because of the overridden queryDatabaseForModelByKey, the findModelById() method will
        // return all models including soft deleted ones.

        // Return that result as long as it IS soft deleted.
        if ($model = $this->findModelById($modelId)) {
            return $model->trashed() ? $model : null;
        }

        return null;
    }

    /**
     * Return a model by its primary key or throw an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findOneBy(string $field, $value): ?Model
    {
        // Because of the overridden queryDatabaseForModelByField, the parent's findOneBy() method will
        // return all models including soft deleted ones.

        // Return that result as long as it is NOT soft deleted.
        if ($model = parent::findOneBy($field, $value)) {
            return $model->trashed() ? null : $model;
        }

        return null;
    }

    /**
     * Return a model by matching the specified field that MAY be soft deleted.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findOneByWithTrashed(string $field, $value): ?Model
    {
        // Because of the overridden queryDatabaseForModelByField, the parent's findOneBy() method will
        // return all models including soft deleted ones.

        // Return that result, because we want both soft deleted and not soft deleted here.
        return parent::findOneBy($field, $value);
    }

    /**
     * Return a model by matching the specified field that MUST be soft deleted.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findTrashedOneBy(string $field, $value): ?Model
    {
        // Because of the overridden queryDatabaseForModelByField, the parent's findOneBy() method will
        // return all models including soft deleted ones.

        // Return that result as long as it is IS soft deleted.
        if ($model = parent::findOneBy($field, $value)) {
            return $model->trashed() ? $model : null;
        }

        return null;
    }

    /**
     * @param mixed $modelId
     *
     * @return Model|null
     */
    protected function queryDatabaseForModelByKey($modelId)
    {
        return $this->create()->newQuery()->withTrashed()->find($modelId);
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    protected function queryDatabaseForModelByField(string $field, $value)
    {
        return $this->create()->newQuery()->withTrashed()->where($field, $value)->first();
    }
}

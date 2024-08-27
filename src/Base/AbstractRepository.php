<?php

namespace Antriver\LaravelRepositories\Base;

use Closure;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Antriver\LaravelRepositories\Base\Traits\FindModelsOrFailTrait;
use Antriver\LaravelRepositories\Exceptions\ModelNotFoundException;
use Antriver\LaravelRepositories\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    use FindModelsOrFailTrait;

    /**
     * If set, this method will be called to create a "not found" exception rather than throwing Laravel's
     * default ModelNotFoundException with a default message.
     * The function receives 3 arguments: The fully qualified class name, the field being looked up, and the vale.
     * Use the setModelNotFoundExceptionFactory to set this easily.
     *
     * @var Closure|null
     */
    protected static $modelNotFoundExceptionFactory;

    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return string
     */
    abstract public function getModelClass(): string;

    /**
     * Return a new instance of this model.
     *
     * @return Model
     */
    public function create(): Model
    {
        $class = $this->getModelClass();

        return new $class;
    }

    /**
     * Return all of this model.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model[]
     */
    public function all()
    {
        return $this->create()->all();
    }

    /**
     * Return a model by its primary key.
     *
     * @param int $modelId
     *
     * @return Model|null
     */
    public function find(int $modelId): ?Model
    {
        if (empty($modelId)) {
            return null;
        }

        return $this->queryDatabaseForModelByKey($modelId);
    }

    /**
     * Return a multiple models by their primary keys.
     *
     * @param int[] $modelIds
     *
     * @return Model[]|Collection
     */
    public function findMany(array $modelIds)
    {
        $modelIds = array_filter(
            $modelIds,
            function ($value) {
                return !empty($value);
            }
        );

        return $this->queryDatabaseForModelsByKey($modelIds);
    }

    /**
     * Return a model by the value of a field.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    public function findOneBy(string $field, $value): ?Model
    {
        if (empty($field)) {
            throw new InvalidArgumentException("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        return $this->queryDatabaseForModelByField($field, $value);
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function persist(Model $model)
    {
        $previousWasRecentlyCreated = $model->wasRecentlyCreated;

        $originalDirtyAttributes = $this->getDirtyOriginalValues($model);

        if (!$model->save()) {
            return false;
        }

        // The model may be saved multiple times in the same request.
        // So check if this is the first time it was created.
        $isNew = !$previousWasRecentlyCreated && $model->wasRecentlyCreated;
        if ($isNew) {
            $this->onInsert($model);
        } else {
            $this->onUpdate($model, $originalDirtyAttributes);
        }

        $this->onChange($model, $originalDirtyAttributes);

        return true;
    }

    /**
     * Fetch a fresh copy of the model from the database.
     *
     * @param Model $model
     *
     * @return Model
     */
    public function fresh(Model $model): Model
    {
        return $model->fresh();
    }

    /**
     * Delete a model from the database.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function remove(Model $model): bool
    {
        $originalDirtyAttributes = $this->getDirtyOriginalValues($model);

        $result = !!$model->delete();

        if ($result) {
            $this->onDelete($model);
            $this->onChange($model, $originalDirtyAttributes);
        }

        return $result;
    }

    /**
     * Atomically increment the specified column of the model. Returns the model with the new value.
     *
     * @param Model $model
     * @param string $column
     * @param int $amount
     *
     * @return Model
     */
    public function increment(Model $model, $column, $amount = 1): Model
    {
        $this->incrementOrDecrement($model, $column, $amount);

        return $this->find($model->getKey());
    }

    /**
     * Atomically decrement the specified column of the model. Returns the model with the new value.
     *
     * @param Model $model
     * @param string $column
     * @param int $amount
     *
     * @return Model
     */
    public function decrement(Model $model, $column, $amount = 1): Model
    {
        return $this->increment($model, $column, -$amount);
    }

    /**
     * Atomically adjust the specified column of the model. Returns the model with the new value.
     *
     * @param Model $model
     * @param string $column
     * @param int $amount
     *
     * @return bool
     */
    protected function incrementOrDecrement(Model $model, $column, $amount = 1): bool
    {
        $originalAttributes = [
            $column => $model->{$column},
        ];

        $amount = (int) $amount;

        $query = "UPDATE `{$model->getTable()}`
        SET `{$column}` = `{$column}` + {$amount}
        WHERE `{$model->getKeyName()}` = ?";

        $result = DB::affectingStatement($query, [$model->getKey()]);

        $this->onUpdate($model, $originalAttributes);
        $this->onChange($model, $originalAttributes);

        $model->{$column} += $amount;

        return $result > 0;
    }

    /**
     * Called when a model is saved for the first time.
     *
     * @param Model $model
     */
    protected function onInsert(Model $model)
    {
        // Does nothing by default.
    }

    /**
     * Called when an existing model is updated.
     *
     * @param Model $model
     * @param array $dirtyAttributes Array of attributes that were changed, and their previous value.
     */
    protected function onUpdate(Model $model, array $dirtyAttributes = null)
    {
        // Does nothing by default.
    }

    /**
     * Called when a model is deleted.
     *
     * @param Model $model
     */
    protected function onDelete(Model $model)
    {
        // Does nothing by default.
    }

    /**
     * Called when the model is inserted, updated, or deleted. After the onInsert/onUpdate/onDelete methods are called.
     *
     * @param Model $model
     * @param array $dirtyAttributes Array of attributes that were changed, and their previous value.
     *                               (Will be empty when deleting)
     *
     */
    protected function onChange(Model $model, array $dirtyAttributes = [])
    {
        // Does nothing by default.
    }

    /**
     * @param int $modelId
     *
     * @return Model|null
     */
    protected function queryDatabaseForModelByKey(int $modelId): ?Model
    {
        return $this->create()->newQuery()->find($modelId);
    }

    /**
     * @param int[] $modelIds
     *
     * @return Model[]|Collection
     */
    protected function queryDatabaseForModelsByKey(array $modelIds)
    {
        return $this->create()->newQuery()->findMany($modelIds);
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    protected function queryDatabaseForModelByField(string $field, $value): ?Model
    {
        return $this->create()->newQuery()->where($field, $value)->first();
    }

    /**
     * @return string
     */
    public function getModelKeyName(): string
    {
        return $this->create()->getKeyName();
    }

    /**
     * @return string
     */
    public function getModelClassWithoutNamespace(): string
    {
        return self::removeNamespaceFromClass($this->getModelClass());
    }

    public static function removeNamespaceFromClass(string $className): string
    {
        $string = explode('\\', $className);

        return array_pop($string);
    }

    /**
     * @param string $field
     * @param string $value
     *
     * @return ModelNotFoundException
     */
    public function createNotFoundException($value, $field = 'id'): Exception
    {
        if ($field === null) {
            $field = $this->getModelKeyName();
        }

        if (self::$modelNotFoundExceptionFactory instanceof Closure) {
            return (self::$modelNotFoundExceptionFactory)($this->getModelClass(), $field, $value);
        }

        return (new ModelNotFoundException())
            ->setModel($this->getModelClass(), $value)
            ->setField($field);
    }

    /**
     * Return which attributes of a model are dirty, and what their original value was.
     *
     * @param Model $model
     *
     * @return array
     */
    protected function getDirtyOriginalValues(Model $model): array
    {
        // Returns the names of dirty attributes and their *current* values.
        $dirtyAttributes = $model->getDirty();

        // Get the original values of the dirty attributes.
        $originalDirtyAttributes = [];
        foreach ($dirtyAttributes as $attributeName => $currentValue) {
            $originalDirtyAttributes[$attributeName] = $model->getOriginal($attributeName);
        }

        return $originalDirtyAttributes;
    }

    /**
     * @param Closure|null $modelNotFoundExceptionFactory
     */
    public static function setModelNotFoundExceptionFactory(?Closure $modelNotFoundExceptionFactory)
    {
        self::$modelNotFoundExceptionFactory = $modelNotFoundExceptionFactory;
    }
}

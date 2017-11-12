<?php

namespace Tmd\LaravelRepositories\Base;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Tmd\LaravelRepositories\Base\Traits\FindModelsOrFailTrait;
use Tmd\LaravelRepositories\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    use FindModelsOrFailTrait;

    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return string
     */
    abstract public function getModelClass();

    /**
     * Return a new instance of this model.
     *
     * @return Model
     */
    public function create()
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
     * @param mixed $modelId
     *
     * @return Model|null
     */
    public function find($modelId): ?Model
    {
        if (empty($modelId)) {
            return null;
        }

        return $this->queryDatabaseForModelByKey($modelId);
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
    public function persist(Model $model): bool
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
     * @return Model|null
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
     * @return Model|null
     */
    public function increment(Model $model, $column, $amount = 1)
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
     * @return Model|null
     */
    public function decrement(Model $model, $column, $amount = 1)
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
     * @return Model|null
     */
    protected function incrementOrDecrement(Model $model, $column, $amount = 1)
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

        return $result;
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
     * @param mixed $modelId
     *
     * @return Model|null
     */
    protected function queryDatabaseForModelByKey($modelId)
    {
        return $this->create()->newQuery()->find($modelId);
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     */
    protected function queryDatabaseForModelByField(string $field, $value)
    {
        return $this->create()->newQuery()->where($field, $value)->first();
    }

    public function getModelKeyName()
    {
        return $this->create()->getKeyName();
    }

    /**
     * @return string
     */
    public function getModelClassWithoutNamespace()
    {
        $string = explode('\\', $this->getModelClass());

        return array_pop($string);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $message
     *
     * @return ModelNotFoundException
     */
    public function createNotFoundException($value, $field = null, $message = null): ModelNotFoundException
    {
        if (!$message) {
            if ($field === null) {
                $field = $this->getModelKeyName();
            }
            $message = "{$this->getModelClass()} with {$field} {$value} not found.";
        }

        return new ModelNotFoundException($message, 404);
    }

    /**
     * Return which attributes of a model are dirty, and what their original value was.
     *
     * @param Model $model
     *
     * @return array
     */
    protected function getDirtyOriginalValues(Model $model)
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
}

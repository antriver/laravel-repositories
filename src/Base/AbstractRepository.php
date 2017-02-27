<?php

namespace Tmd\LaravelRepositories\Base;

use DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tmd\LaravelRepositories\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return string
     */
    abstract public function getModelClass();

    /**
     * Return a new instance of this model.
     *
     * @return EloquentModel
     */
    public function create()
    {
        $class = $this->getModelClass();

        return new $class;
    }

    /**
     * Return all of this model.
     *
     * @return \Illuminate\Database\Eloquent\Collection|EloquentModel[]
     */
    public function all()
    {
        return $this->create()->all();
    }

    /**
     * Return a model by its primary key.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function find($key)
    {
        if (empty($key)) {
            return null;
        }

        return $this->queryDatabaseForModelByKey($key);
    }

    /**
     * Return a model by its primary key. Throws an exception if not found.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail($key)
    {
        if ($model = $this->find($key)) {
            return $model;
        }

        throw $this->createNotFoundException($this->create()->getKeyName(), $key);
    }

    /**
     * Return a model by the value of a field.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     * @throws \Exception
     */
    public function findOneBy($field, $value)
    {
        if (empty($field)) {
            throw new \Exception("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        return $this->queryDatabaseForModelByField($field, $value);
    }

    /**
     * Return a model by the value of a field. Throws an exception if not found.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     *
     * @throws ModelNotFoundException
     */
    public function findOneByOrFail($field, $value)
    {
        if ($model = $this->findOneBy($field, $value)) {
            return $model;
        }
        throw $this->createNotFoundException($field, $value);
    }

    /**
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function persist(EloquentModel $model)
    {
        $wasPreviouslyRecentlyCreated = $model->wasRecentlyCreated;

        // getDirty returns the current value of the attributes.
        $dirtyAttributes = $model->getDirty();

        // Get the original values of the dirty attributes.
        $originalDirtyAttributes = [];
        foreach ($dirtyAttributes as $key => $newValue) {
            $originalDirtyAttributes[$key] = $model->getOriginal($key);
        }

        if (!$model->save()) {
            return false;
        }

        // The model may be saved multiple times in the same request.
        // So check if this is the first time it was created.
        $isNew = !$wasPreviouslyRecentlyCreated && $model->wasRecentlyCreated;
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
     * @param EloquentModel $model
     *
     * @return EloquentModel|null
     */
    public function fresh(EloquentModel $model)
    {
        return $model->fresh();
    }

    /**
     * Delete a model from the database.
     *
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function remove(EloquentModel $model)
    {
        $result = !!$model->delete();

        if ($result) {
            $this->onDelete($model);
            $this->onChange($model);
        }

        return $result;
    }

    /**
     * Atomically increment the specified column of the model. Returns the model with the new value.
     *
     * @param EloquentModel $model
     * @param string        $column
     * @param int           $amount
     *
     * @return EloquentModel|null
     */
    public function increment(EloquentModel $model, $column, $amount = 1)
    {
        $this->incrementOrDecrement($model, $column, $amount);

        return $this->find($model->getKey());
    }

    /**
     * Atomically decrement the specified column of the model. Returns the model with the new value.
     *
     * @param EloquentModel $model
     * @param string        $column
     * @param int           $amount
     *
     * @return EloquentModel|null
     */
    public function decrement(EloquentModel $model, $column, $amount = 1)
    {
        return $this->increment($model, $column, -$amount);
    }

    /**
     * Atomically adjust the specified column of the model. Returns the model with the new value.
     *
     * @param EloquentModel $model
     * @param string        $column
     * @param int           $amount
     *
     * @return EloquentModel|null
     */
    protected function incrementOrDecrement(EloquentModel $model, $column, $amount = 1)
    {
        $originalAttributes = [
            $column => $model->{$column}
        ];

        $amount = (int)$amount;

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
     * @param EloquentModel $model
     */
    protected function onInsert(EloquentModel $model)
    {
       // Does nothing by default.
    }

    /**
     * Called when an existing model is updated.
     *
     * @param EloquentModel $model
     * @param array         $dirtyAttributes Array of attributes that were changed, and their previous value.
     */
    protected function onUpdate(EloquentModel $model, array $dirtyAttributes = null)
    {
        // Does nothing by default.
    }

    /**
     * Called when a model is deleted.
     *
     * @param EloquentModel $model
     */
    protected function onDelete(EloquentModel $model)
    {
        // Does nothing by default.
    }

    /**
     * Called when the model is inserted, updated, or deleted. After the onInsert/onUpdate/onDelete methods are called.
     *
     * @param EloquentModel $model
     * @param array         $dirtyAttributes Array of attributes that were changed, and their previous value.
     */
    protected function onChange(EloquentModel $model, array $dirtyAttributes = null)
    {
        // Does nothing by default.
    }

    /**
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    protected function queryDatabaseForModelByKey($key)
    {
        return $this->create()->newQuery()->find($key);
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    protected function queryDatabaseForModelByField($field, $value)
    {
        return $this->create()->newQuery()->where($field, $value)->first();
    }

    /**
     * @return string
     */
    protected function getModelClassWithoutNamespace()
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
    protected function createNotFoundException($field, $value, $message = null)
    {
        if (!$message) {
            $message = "{$this->getModelClass()} with {$field} {$value} not found.";
        }

        return new ModelNotFoundException($message, 404);
    }
}

<?php

namespace Tmd\LaravelRepositories\Base;

use Closure;
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
     * @var Closure|null
     */
    public $inserted = null;

    /**
     * @var Closure|null
     */
    public $updated = null;

    /**
     * @var Closure|null
     */
    public $deleted = null;

    /**
     * @var Closure|null
     */
    public $changed = null;

    /**
     * @return EloquentModel
     */
    protected function getModelInstance()
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
        return $this->getModelInstance()->all();
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
        if ($model = $this->queryDatabaseForModelByKey($key)) {
            return $model;
        }

        throw $this->createNotFoundException($this->getModelInstance()->getKeyName(), $key);
    }

    /**
     * Return a model by the value of a field.
     *
     * @param string $field
     *
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findOneBy($field, $value)
    {
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
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    protected function queryDatabaseForModelByKey($key)
    {
        return $this->getModelInstance()->newQuery()->find($key);
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    protected function queryDatabaseForModelByField($field, $value)
    {
        return $this->getModelInstance()->newQuery()->where($field, $value)->first();
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
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function persist(EloquentModel $model)
    {
        $oldWasRecentlyCreated = $model->wasRecentlyCreated;

        $dirtyAttributes = $model->getDirty();
        $originalAttributes = [];
        foreach ($dirtyAttributes as $key => $newValue) {
            $originalAttributes[$key] = $model->getOriginal($key);
        }

        if (!$model->save()) {
            return false;
        }

        $isNew = !$oldWasRecentlyCreated && $model->wasRecentlyCreated;


        if ($isNew) {
            $this->onInsert($model);
        } else {
            $this->onUpdate($model, $originalAttributes);
        }

        $this->onChange($model, $originalAttributes);

        return true;
    }

    /**
     * Reload a fresh model instance from the database.
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

    public function increment(EloquentModel $model, $column, $amount = 1)
    {
        $this->incrementOrDecrement($model, $column, $amount);

        return $this->find($model->getKey());
    }

    public function decrement(EloquentModel $model, $column, $amount = 1)
    {
        return $this->increment($model, $column, -$amount);
    }

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

        return $result;
    }

    /**
     * Called when a model is saved for the first time.
     *
     * @param EloquentModel $model
     */
    protected function onInsert(EloquentModel $model)
    {
        if ($this->inserted instanceof Closure) {
            call_user_func($this->inserted, $model);
        }
    }

    /**
     * Called when an existing model is updated.
     *
     * @param EloquentModel $model
     * @param array         $dirtyAttributes Array of attributes that were changed, and their previous value.
     */
    protected function onUpdate(EloquentModel $model, array $dirtyAttributes = null)
    {
        if ($this->updated instanceof Closure) {
            call_user_func($this->updated, $model, $dirtyAttributes);
        }
    }

    /**
     * Called when a model is deleted.
     *
     * @param EloquentModel $model
     */
    protected function onDelete(EloquentModel $model)
    {
        if ($this->deleted instanceof Closure) {
            call_user_func($this->deleted, $model);
        }
    }

    /**
     * Called when the model is inserted, updated, or deleted. After the onInsert/onUpdate/onDelete methods are called.
     *
     * @param EloquentModel $model
     * @param array         $dirtyAttributes Array of attributes that were changed, and their previous value.
     */
    protected function onChange(EloquentModel $model, array $dirtyAttributes = null)
    {
        if ($this->changed instanceof Closure) {
            call_user_func($this->changed, $model, $dirtyAttributes);
        }
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

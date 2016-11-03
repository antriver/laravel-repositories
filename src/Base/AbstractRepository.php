<?php

namespace Tmd\LaravelRepositories\Base;

use DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tmd\LaravelRepositories\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Return the fully qualified class name of the Models this repository returns.
     *
     * @return EloquentModel|string|\Illuminate\Database\Eloquent\Builder
     */
    abstract public function getModelClass();

    /**
     * Return a model by its primary key.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function find($key)
    {
        return $this->queryModelByKey($key);
    }

    /**
     * Return a model by its primary key. Throws an exception if not found.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     *
     * @throws NotFoundHttpException
     */
    public function findOrFail($key)
    {
        if ($model = $this->queryModelByKey($key)) {
            return $model;
        }

        $class = $this->getModelClass();
        $this->throwNotFoundException((new $class)->getKeyName(), $key);

        return null;
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
        return $this->queryModelByField($field, $value);
    }

    /**
     * Return a model by the value of a field. Throws an exception if not found.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findOneByOrFail($field, $value)
    {
        if ($model = $this->queryModelByField($field, $value)) {
            return $model;
        }
        $this->throwNotFoundException($field, $value);

        return null;
    }

    /**
     * Return all of this model.
     *
     * @return \Illuminate\Database\Eloquent\Collection|EloquentModel[]
     */
    public function all()
    {
        $class = $this->getModelClass();

        return $class::all();
    }

    /**
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function persist(EloquentModel $model)
    {
        $oldWasRecentlyCreated = $model->wasRecentlyCreated;

        if (!$model->save()) {
            return false;
        }

        $isNew = !$oldWasRecentlyCreated && $model->wasRecentlyCreated;

        if ($isNew) {
            $this->onInsert($model);
        } else {
            $this->onUpdate($model);
        }

        $this->onChange($model);

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
        $amount = (int)$amount;

        $query = "UPDATE `{$model->getTable()}`
        SET `{$column}` = `{$column}` + {$amount}
        WHERE `{$model->getKeyName()}` = ?";

        return DB::affectingStatement($query, [$model->getKey()]);
    }

    /**
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    protected function queryModelByKey($key)
    {
        $class = $this->getModelClass();

        return $class::find($key);
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    protected function queryModelByField($field, $value)
    {
        $class = $this->getModelClass();

        return $class::where($field, $value)->first();
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
     * Called when a model is saved for he first time.
     *
     * @param EloquentModel $model
     */
    protected function onInsert(EloquentModel $model)
    {

    }

    /**
     * Called when an existing model is updated.
     *
     * @param EloquentModel $model
     */
    protected function onUpdate(EloquentModel $model)
    {

    }

    /**
     * Called when a model is deleted.
     *
     * @param EloquentModel $model
     */
    protected function onDelete(EloquentModel $model)
    {

    }

    /**
     * Called when the model is inserted, updated, or deleted.
     * (AFTER the onInsert/onUpdate/onDelete methods are called.)
     *
     * @param EloquentModel $model
     */
    protected function onChange(EloquentModel $model)
    {

    }

    /**
     * @param string $field
     * @param string $value
     * @param string $message
     */
    protected function throwNotFoundException($field, $value, $message = null)
    {
        if (!$message) {
            $message = "{$this->getModelClass()} with {$field} {$value} not found.";
        }
        throw new NotFoundHttpException($message);
    }
}

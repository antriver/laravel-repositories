<?php

namespace Tmd\LaravelModelRepositories\Base;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class AbstractModelRepository implements RepositoryInterface
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
     * @param int $key
     *
     * @return EloquentModel|null
     */
    public function find($key)
    {
        $class = $this->getModelClass();

        return $class::find($key);
    }

    /**
     * Return a model by its primary key. Throws an exception if not found.
     *
     * @param int $key
     *
     * @return EloquentModel|null
     *
     * @throws NotFoundHttpException
     */
    public function findOrFail($key)
    {
        if ($model = $this->find($key)) {
            return $model;
        }
        $this->throwNotFoundException($this->getModelClass()->getKeyName(), $key);

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
    public function findBy($field, $value)
    {
        /** @var AbstractModelRepository $this */
        $class = $this->getModelClass();

        // Look up the model by slug
        return $class::where($field, $value)->first();
    }

    /**
     * Return a model by the value of a field. Throws an exception if not found.
     *
     * @param string $field
     *
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findByOrFail($field, $value)
    {
        if ($model = $this->findBy($field, $value)) {
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

    protected function getModelClassWithoutNamespace()
    {
        $string = explode('\\', $this->getModelClass());

        return array_pop($string);
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

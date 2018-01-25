<?php

namespace Tmd\LaravelRepositories\Base\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tmd\LaravelRepositories\Base\AbstractRepository;

trait FindModelsOrFailTrait
{
    /**
     * Return a model by its primary key. Throws an exception if not found.
     *
     * @param int $modelId
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $modelId)
    {
        /** @var AbstractRepository|self $this */

        if ($model = $this->find($modelId)) {
            return $model;
        }

        throw $this->createNotFoundException($modelId);
    }

    /**
     * Return a model by the value of a field. Throws an exception if not found.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOneByOrFail(string $field, $value)
    {
        /** @var AbstractRepository|self $this */

        if ($model = $this->findOneBy($field, $value)) {
            return $model;
        }

        throw $this->createNotFoundException($value, $field);
    }
}

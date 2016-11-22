<?php

namespace Tmd\LaravelRepositories\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRepositories\Base\AbstractRepository;

/**
 * Implementation of SoftDeletableRepositoryInterface
 */
trait SoftDeletableRepositoryTrait
{
    use SoftDeletableOrFailTrait;

    /**
     * Return a model that MAY be soft deleted.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findWithTrashed($key)
    {
        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::withTrashed()->find($key);
    }

    /**
     * Return a model that MUST be soft deleted.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function findTrashed($key)
    {
        /** @var AbstractRepository $this */
        $class = $this->getModelClass();

        return $class::onlyTrashed()->find($key);
    }
}

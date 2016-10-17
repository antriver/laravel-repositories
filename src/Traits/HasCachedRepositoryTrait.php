<?php

namespace Tmd\LaravelRepositories\Traits;

use ReflectionClass;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRepositories\Base\AbstractCachedRepository;

/**
 * Use this trait on a model to automatically refresh the cached copy when the model is updated or deleted.
 */
trait HasCachedRepositoryTrait
{
    public static function bootHasCachedRepositoryTrait()
    {
        $forgetInRepository = function (EloquentModel $model) {
            if (method_exists($model, 'getRepository')) {
                $repository = $model->getRepository();
            } else {
                // Determine the name of the repository
                $reflect = new ReflectionClass($model);
                $repositoryName = $reflect->getShortName().'Repository';
                $repository = app($repositoryName);
            }

            if ($repository instanceof AbstractCachedRepository) {
                $repository->refresh($model->getKey());
                $repository->forgetFieldKeys($model);
            }
        };

        self::created($forgetInRepository);
        self::updated($forgetInRepository);
        self::deleted($forgetInRepository);
    }
}

<?php

namespace Antriver\LaravelRepositories\Providers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\ServiceProvider;
use Antriver\LaravelRepositories\Base\AbstractRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register which models are provided by which repositories.
     *
     * @var array
     */
    protected static $repositories = [];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        AbstractRepository::setModelNotFoundExceptionFactory(
            function ($class) {
                $shortClass = strtolower(AbstractRepository::removeNamespaceFromClass($class));
                $str = "The {$shortClass} you were looking for could not be found.";

                return new ModelNotFoundException($str);
            }
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        foreach (self::$repositories as $repositoryClass => $modelClasses) {
            $this->app->singleton($repositoryClass, $repositoryClass);
        }
    }

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys(self::$repositories);
    }

    /**
     * @param string $modelClass
     *
     * @return string|null
     */
    public static function findRepositoryClassForModelClass(string $modelClass): ?string
    {
        foreach (self::$repositories as $repositoryClass => $modelClasses) {
            if (in_array($modelClass, $modelClasses)) {
                return $repositoryClass;
            }
        }

        return null;
    }

    /**
     * @param string $modelClass
     *
     * @return AbstractRepository|null
     */
    public static function findRepositoryForModelClass($modelClass): ?AbstractRepository
    {
        if ($repositoryClass = self::findRepositoryClassForModelClass($modelClass)) {
            return app($repositoryClass);
        }

        return null;
    }

    /**
     * @param object $model
     *
     * @return AbstractRepository|null
     */
    public static function findRepositoryForModel($model): ?AbstractRepository
    {
        $modelClass = get_class($model);

        return self::findRepositoryForModelClass($modelClass);
    }
}

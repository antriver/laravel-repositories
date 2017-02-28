<?php

namespace Tmd\LaravelRepositories\Base;

use Cache;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRepositories\Interfaces\CachedRepositoryInterface;

abstract class AbstractCachedRepository extends AbstractRepository implements CachedRepositoryInterface
{
    /**
     * @var Store
     */
    protected $cache = null;

    /**
     * @param Store|null $cache
     */
    public function __construct(Store $cache = null)
    {
        if ($cache) {
            $this->cache = $cache;
        } else {
            // Use the default cache store.
            $this->cache = Cache::getStore();
        }
    }

    /**
     * Return a model by its primary key.
     * If the model is not found, 'false' is cached to remember that it does not exist.
     * But the method will always return a model or null.
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

        $cacheKey = $this->getCacheKey($key);

        $modelOrFalse = $this->cache->get($cacheKey);
        if ($modelOrFalse !== null) {
            return $modelOrFalse === false ? null : $modelOrFalse;
        }

        $model = $this->queryDatabaseForModelByKey($key);

        $this->storeInCache($key, $model);

        return $model ?: null;
    }

    /**
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

        // See if we already have the key for this field value cached.
        // If so, load it by key instead as the whole model may be cached that way.
        $idCacheKey = $this->getKeyForFieldCacheKey($field, $value);
        if ($idCacheKey && $id = $this->cache->get($idCacheKey)) {
            // Sweet, we know the key - look up the model using that.
            return $this->find($id);
        }

        $model = $this->queryDatabaseForModelByField($field, $value);

        // No result
        if (!$model) {
            return null;
        }

        // Remember the model itself (by key)
        $this->remember($model);

        // And remember the key for the field value
        $this->cache->forever($idCacheKey, $model->getKey());

        return $model;
    }

    /**
     * Remove the cached copy of a model.
     * Note that cached primary keys for other field lookups need to be forgotten too, but this does not do that.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function forget($key)
    {
        $cacheKey = $this->getCacheKey($key);

        return $this->cache->forget($cacheKey);
    }

    /**
     * Store the given model in the cache.
     *
     * @param EloquentModel|false $model
     */
    public function remember($model)
    {
        $this->storeInCache($model->getKey(), $model);
    }

    /**
     * Override this method to forget the cached values of $this->getKeyForFieldCacheKey if used.
     *
     * @param EloquentModel $model
     */
    public function forgetFieldKeys(EloquentModel $model)
    {

    }

    /**
     * Update the cached copy of a model.
     *
     * @param mixed $key
     *
     * @return EloquentModel|null
     */
    public function refresh($key)
    {
        $model = parent::find($key);
        $this->remember($model);

        return $model;
    }

    /**
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function persist(EloquentModel $model)
    {
        if (parent::persist($model)) {
            $this->remember($this->fresh($model));

            return true;
        }

        return false;
    }

    /**
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function remove(EloquentModel $model)
    {
        $result = parent::remove($model);
        if ($result) {
            $this->forgetFieldKeys($model);
            $this->forget($model->getKey());
        }

        return $result;
    }

    public function incrementOrDecrement(EloquentModel $model, $column, $amount = 1)
    {
        parent::incrementOrDecrement($model, $column, $amount);

        return $this->refresh($model->getKey());
    }

    /**
     * Store a model (or remember its lack of existence) in the cache.
     *
     * @param mixed              $key
     * @param EloquentModel|null $model
     */
    protected function storeInCache($key, $model)
    {
        $cacheKey = $this->getCacheKey($key);

        $this->cache->forever($cacheKey, $model ?: false);
    }

    /**
     * Return the unique string to use as the cache key for this model.
     * Default is the lowercase model class name.
     *
     * @param string $modelKey
     *
     * @return string
     */
    protected function getCacheKey($modelKey)
    {
        return strtolower($this->getModelClassWithoutNamespace()).':'.$modelKey;
    }

    /**
     * Return the unique string to use to cache the primary key of a model looked up by a field.
     * e.g. For looking up users by username: user-username-key:anthony.
     * Return null to not cache.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string|null
     */
    protected function getKeyForFieldCacheKey($field, $value)
    {
        return strtolower($this->getModelClassWithoutNamespace().'-'.$field).'-key:'.strtolower($value);
    }
}

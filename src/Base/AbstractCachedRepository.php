<?php

namespace Tmd\LaravelRepositories\Base;

use Cache;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRepositories\Events\ArrayCacheHit;
use Tmd\LaravelRepositories\Events\ArrayCacheMissed;
use Tmd\LaravelRepositories\Events\ArrayCacheWritten;
use Tmd\LaravelRepositories\Interfaces\CachedRepositoryInterface;

abstract class AbstractCachedRepository extends AbstractRepository implements CachedRepositoryInterface
{
    /**
     * Once we've loaded an item from the cache or database, remember it in an array in the repository?
     *
     * @var bool
     */
    protected $useLocalCache = true;

    /**
     * @var EloquentModel[]
     */
    protected $localCache = [];

    /**
     * @var bool
     */
    protected $fireLocalCacheEvents = false;

    /**
     * Returns a single model by its ID.
     * If the model is not found, a boolean false is cached to remember that it does not exist. But the method will
     * always return a model or null.
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

        if ($this->useLocalCache && array_key_exists($cacheKey, $this->localCache)) {
            if ($this->fireLocalCacheEvents) {
                event(new ArrayCacheHit($cacheKey));
            }
            $modelOrFalse = $this->localCache[$cacheKey];

            return $modelOrFalse ?: null;
        } elseif ($this->useLocalCache && $this->fireLocalCacheEvents) {
            event(new ArrayCacheMissed($cacheKey));
        }

        if (!is_null($modelOrFalse = Cache::get($cacheKey))) {
            if ($this->useLocalCache) {
                $this->localCache[$cacheKey] = $modelOrFalse;
                if ($this->fireLocalCacheEvents) {
                    event(new ArrayCacheWritten($cacheKey));
                }
            }

            return $modelOrFalse ?: null;
        }

        $model = $this->queryModelByKey($key);

        $this->storeInCache($key, $model);

        return $model ?: null;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return EloquentModel|null
     */
    public function findBy($field, $value)
    {
        // See if we already have the key for this field value cached.
        // If so, load it by key instead as the whole model may be cached that way.
        $idCacheKey = $this->getKeyForFieldCacheKey($field, $value);
        if ($idCacheKey && $id = Cache::get($idCacheKey)) {
            // Sweet, we know the key - look up the model using that.
            return $this->find($id);
        }

        $model = $this->queryModelByField($field, $value);

        // No result
        if (!$model) {
            return null;
        }

        // Remember the model itself (by key)
        $this->storeInCache($model->getKey(), $model);

        // And remember the key for the field value
        Cache::forever($idCacheKey, $model->getKey());

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
        if (array_key_exists($key, $this->localCache)) {
            unset($this->localCache[$key]);
        }

        return Cache::forget($this->getCacheKey($key));
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
     */
    public function refresh($key)
    {
        $model = parent::find($key);
        $this->storeInCache($key, $model);
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

        Cache::forever($cacheKey, $model ?: false);

        if ($this->useLocalCache) {
            $this->localCache[$cacheKey] = $model ?: false;
            if ($this->fireLocalCacheEvents) {
                event(new ArrayCacheWritten($cacheKey));
            }
        }
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

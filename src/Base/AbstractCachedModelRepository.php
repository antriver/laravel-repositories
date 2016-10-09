<?php

namespace Tmd\LaravelModelRepositories\Base;

use Cache;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class AbstractCachedModelRepository extends AbstractModelRepository
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
     * Return the unique string to use as the cache key for this model.
     *
     * @param string $modelKey
     *
     * @return string
     */
    protected function getCacheKey($modelKey)
    {
        return strtolower($this->getModelClass()).':'.$modelKey;
    }

    /**
     * Return the unique string to use to cache the primary key of a model looked up by a field.
     * e.g. For looking up users by username: userId-username-anthony.
     * Return null to not cache.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return string|null
     */
    protected function getKeyForFieldCacheKey($field, $value)
    {
        return strtolower($this->getModelClass().'-'.$field).'-key:'.$value;
    }


    /**
     * Returns a single model by its ID.
     * If the model is not found, a boolean false is cached to remember that it does not exist. But the method will
     * always return a model or null.
     *
     * @param int $id
     *
     * @return EloquentModel|null
     */
    public function find($id)
    {
        $id = (int)$id;

        if (empty($id)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($id);

        if ($this->useLocalCache && array_key_exists($cacheKey, $this->localCache)) {
            $modelOrFalse = $this->localCache[$cacheKey];

            return $modelOrFalse ?: null;
        }

        if (!is_null($modelOrFalse = Cache::get($cacheKey))) {
            if ($this->useLocalCache) {
                $this->localCache[$cacheKey] = $modelOrFalse;
            }

            return $modelOrFalse ?: null;
        }

        $model = parent::find($id);

        $this->storeInCache($id, $model);

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
        $idCacheKey = $this->getKeyForFieldCacheKey($field, $value);

        // See if the id for the slug is already in the cache
        if ($idCacheKey && $id = Cache::get($idCacheKey)) {
            // Sweet, we know the id - look up the model using that
            return $this->find($id);
        }

        $model = parent::findBy($field, $value);

        if (!$model) {
            // No result

            return null;
        }

        // Remember the model itself (by id)
        $this->storeInCache($model->getKey(), $model);

        // And remember the primary key for the slug
        Cache::forever($idCacheKey, $model->getKey());

        return $model;
    }

    /**
     * Store a model (or remember its lack of existence) in the cache.
     *
     * @param int                $key
     * @param EloquentModel|null $model
     */
    protected function storeInCache($key, $model)
    {
        $cacheKey = $this->getCacheKey($key);

        Cache::forever($cacheKey, $model ?: false);

        if ($this->useLocalCache) {
            $this->localCache[$cacheKey] = $model ?: false;
        }
    }

    /**
     * Remove the cached copy of a model.
     * Note that cached primary keys for other field lookups need to be forgotten too, but this does not do that.
     *
     * @param int $id
     *
     * @return bool
     */
    public function forget($id)
    {
        if (array_key_exists($id, $this->localCache)) {
            unset($this->localCache[$id]);
        }

        return Cache::forget($this->getCacheKey($id));
    }

    /**
     * Override this method to perform additional cleanup when forgetting a model.
     *
     * @param EloquentModel $model
     */
    public function forgetFieldKeys(EloquentModel $model)
    {

    }

    /**
     * Update the cached copy of a model.
     *
     * @param $id
     */
    public function refresh($id)
    {
        $model = parent::find($id);
        $this->storeInCache($id, $model);
    }

    /**
     * Store a model in the cache.
     *
     * @param EloquentModel $model
     */
    public function remember(EloquentModel $model)
    {
        Cache::forever($this->getCacheKey($model->getKey()), $model);
    }
}

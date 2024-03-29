<?php

namespace Antriver\LaravelRepositories\Base;

use Cache;
use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Antriver\LaravelRepositories\Interfaces\CachedRepositoryInterface;

abstract class AbstractCachedRepository extends AbstractRepository implements CachedRepositoryInterface
{
    /**
     * @var Repository
     */
    protected $cache = null;

    /**
     * @param Repository|null $cache
     */
    public function __construct(Repository $cache = null)
    {
        if ($cache) {
            $this->cache = $cache;
        } else {
            // Use the default cache store.
            $this->cache = Cache::repository(Cache::getStore());
        }
    }

    /**
     * Return a model by its primary key.
     *
     * If the model is not found boolean 'false' is cached to remember that it does not exist.
     * (Thee method will always return a model or null regardless)
     *
     * @param int $modelId
     *
     * @return Model|null
     */
    public function find(int $modelId): ?Model
    {
        return $this->findModelById($modelId);
    }

    /**
     * This is a separate method just called by find() and findOneBy() as we want to be able to
     * override find() in subclasses without any custom functionality also affecting findOneBy()
     *
     * @param int $modelId
     *
     * @return Model|null
     */
    protected function findModelById(int $modelId): ?Model
    {
        if (empty($modelId)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($modelId);

        $modelOrFalse = $this->cache->get($cacheKey);
        if ($modelOrFalse === false) {
            return null;
        } elseif (!empty($modelOrFalse)) {
            return $modelOrFalse;
        }

        $modelResult = $this->queryDatabaseForModelByKey($modelId);

        $this->storeModelResultInCache($modelId, $modelResult);

        return $modelResult ?: null;
    }

    /**
     * Return a multiple models by their primary keys.
     *
     * @param mixed[] $modelIds
     *
     * @return Model[]|Collection
     */
    public function findMany(array $modelIds)
    {
        $modelIds = array_filter(
            $modelIds,
            function ($value) {
                return !empty($value);
            }
        );

        if (empty($modelIds)) {
            return new Collection();
        }

        $models = [];

        // Build an array of all the cache keys for the models.
        $remainingModels = [];
        foreach ($modelIds as &$modelId) {
            $cacheKey = $this->getCacheKey($modelId);
            $remainingModels[$cacheKey] = $modelId;
        }

        // Try to get all the models at once from the cache.
        $cachedResults = $this->cache->many(array_keys($remainingModels));
        foreach ($cachedResults as $cacheKey => $result) {
            // $result will be null, false, or the model.
            if ($result === false) {
                // We know that it does not exist.
                unset($remainingModels[$cacheKey]);
            } elseif ($result) {
                $models[$result->id] = $result;
                unset($remainingModels[$cacheKey]);
            }
        }

        if (count($remainingModels) < 1) {
            return new Collection($models);
        }

        // Get any remaining models from the DB.
        $loadedModels = $this->queryDatabaseForModelsByKey($remainingModels)->keyBy($this->getModelKeyName());
        foreach ($remainingModels as $cacheKey => $modelId) {
            if ($loadedModels->offsetExists($modelId)) {
                $result = $loadedModels->offsetGet($modelId);
                $this->storeModelResultInCache($modelId, $result, $cacheKey);
                $models[$modelId] = $result;
            } else {
                $this->storeModelResultInCache($modelId, null, $cacheKey);
            }
        }

        return new Collection($models);
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @return Model|null
     * @throws Exception
     */
    public function findOneBy(string $field, $value): ?Model
    {
        if (empty($field)) {
            throw new Exception("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        // When using findOneBy() we cache the *key/ID* of the model (e.g. remember that userID 1 is the answer to
        // the user with the username 'Anthony').
        // If we have that ID cached we use the find() method, as the actual model may be cached there too.
        $idCacheKey = $this->getIdForFieldCacheKey($field, $value);
        if ($idCacheKey) {
            $id = $this->cache->get($idCacheKey);
            if ($id === false) {
                // We previously cached that there was no model for this field/value combo.
                return null;
            } elseif ($id) {
                // Sweet, we know the key/ID - look up the model using that.
                return $this->findModelById($id);
            }
        }

        // Query for the model using the field.
        $model = $this->queryDatabaseForModelByField($field, $value);

        // No result
        if (!$model) {
            // TODO: Cache false in the idCacheKey?
            // The retrieval is already implemented above. But need to correctly
            // clear the cached false when appropriate to make this doable.
            //$this->cache->forever($idCacheKey, false);
            return null;
        }

        // Remember the model itself (by ID).
        $this->rememberModel($model);

        // And remember the ID for the field value.
        $this->cache->forever($idCacheKey, $model->getKey());

        return $model;
    }

    /**
     * Remove the cached copy of a model.
     * Note that cached primary keys for other field lookups need to be forgotten too, but this does not do that.
     *
     * @param int $modelId
     *
     * @return bool
     */
    public function forgetById(int $modelId)
    {
        $cacheKey = $this->getCacheKey($modelId);

        return $this->cache->forget($cacheKey);
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function forgetByModel(Model $model)
    {
        return $this->forgetById($model->getKey());
    }

    /**
     * Store the given model in the cache.
     *
     * @param Model $model
     */
    public function rememberModel(Model $model)
    {
        $this->storeModelResultInCache($model->getKey(), $model);
        // TODO: Also cache the ID for certain field values here? e.g. the ID for the user's username.
        // Need a method for subclasses to implement to specify which fields to cache for.
    }

    /**
     * Override this method to forget the cached values of $this->getKeyForFieldCacheKey if used.
     * This is called when removing (deleting) a model.
     * @see getIdForFieldCacheKey()
     *
     * @param Model $model
     */
    public function forgetCachedModelIdForFields(Model $model)
    {

    }

    /**
     * Update the cached copy of a model.
     *
     * @param int $modelId
     *
     * @return Model|null
     */
    public function refreshById(int $modelId)
    {
        $model = parent::find($modelId);
        $this->rememberModel($model);

        return $model;
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function persist(Model $model)
    {
        if (parent::persist($model)) {
            $this->rememberModel($this->fresh($model));

            return true;
        }

        return false;
    }

    public function fresh(Model $model): Model
    {
        $model = parent::fresh($model);

        $this->rememberModel($model);

        return $model;
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function remove(Model $model): bool
    {
        $result = parent::remove($model);
        if ($result) {
            $this->forgetCachedModelIdForFields($model);
            $this->forgetById($model->getKey());
        }

        return $result;
    }

    /**
     * @param Model $model
     * @param string $column
     * @param int $amount
     *
     * @return bool
     */
    public function incrementOrDecrement(Model $model, $column, $amount = 1): bool
    {
        $result = parent::incrementOrDecrement($model, $column, $amount);

        $this->refreshById($model->getKey());

        return $result;
    }

    /**
     * Store a model (or remember its lack of existence) in the cache.
     *
     * @param mixed $modelId
     * @param Model|null $model
     * @param string|null $cacheKey
     */
    protected function storeModelResultInCache($modelId, $model, $cacheKey = null)
    {
        $cacheKey = $cacheKey ?: $this->getCacheKey($modelId);

        $this->cache->forever($cacheKey, ($model ?: false));
    }

    /**
     * Return the unique string to use as the cache key for this model.
     * Default is the lowercase model class name.
     *
     * @param int $modelId
     *
     * @return string
     */
    protected function getCacheKey(int $modelId): string
    {
        return strtolower($this->getModelClassWithoutNamespace()).':'.$modelId;
    }

    /**
     * If using 'findOneBy()', the primary key for the item matching that field will be cached so the full model
     * can be looked up by its primary key.
     * e.g. if you use findOneBy('username', 'Anthony'), the ID 123 can be cached.
     * The key for this cached item is generated here.
     * e.g. 'user-username-id:Anthony'
     *
     * Return null to disable this caching.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return string|null
     */
    protected function getIdForFieldCacheKey(string $field, $value): string
    {
        $valueSlug = md5($value);

        return strtolower($this->getModelClassWithoutNamespace().'-'.$field.'-id:').$valueSlug;
    }
}

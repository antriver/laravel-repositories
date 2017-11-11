<?php

namespace Tmd\LaravelRepositories\Base;

use Cache;
use Exception;
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
     * @param mixed $modelId
     *
     * @return EloquentModel|null
     */
    public function find($modelId)
    {
        if (empty($modelId)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($modelId);

        if (empty($cacheKey)) {
            return null;
        }

        $modelOrFalse = $this->cache->get($cacheKey);
        if ($modelOrFalse !== null) {
            return $modelOrFalse === false ? null : $modelOrFalse;
        }

        $modelResult = $this->queryDatabaseForModelByKey($modelId);

        $this->storeModelResultInCache($modelId, $modelResult);

        return $modelResult ?: null;
    }

    /**
     * @param string $field
     * @param mixed $value
     *
     * @return EloquentModel|null
     * @throws Exception
     */
    public function findOneBy($field, $value)
    {
        if (empty($field)) {
            throw new Exception("A field must be specified.");
        }

        if (empty($value)) {
            return null;
        }

        // When using findOneBy() we cache the *key/ID* of the model (e.g. remember that username 'Anthony'
        // belongs to the user ID 1.
        // If we have that ID cached we use the find() method, as the actual model may be cached there too.
        $idCacheKey = $this->getIdForFieldCacheKey($field, $value);
        $id = $this->cache->get($idCacheKey);
        if ($id === false) {
            // We previously cached that there was no model for this field/value combo.
            return null;
        } elseif ($id) {
            // Sweet, we know the key/ID - look up the model using that.
            return $this->find($id);
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
     * @param mixed $modelId
     *
     * @return bool
     */
    public function forgetById($modelId)
    {
        $cacheKey = $this->getCacheKey($modelId);

        return $this->cache->forget($cacheKey);
    }

    /**
     * @param EloquentModel $model
     *
     * @return bool
     */
    public function forgetByModel(EloquentModel $model)
    {
        return $this->forgetById($model->getKey());
    }

    /**
     * Store the given model in the cache.
     *
     * @param EloquentModel $model
     */
    public function rememberModel(EloquentModel $model)
    {
        $this->storeModelResultInCache($model->getKey(), $model);
        // TODO: Also cache the ID for certain field values here? e.g. the ID for the user's username.
        // Need a method for subclasses to implement to specify which fields to cache for.
    }

    /**
     * Override this method to forget the cached values of $this->getKeyForFieldCacheKey if used.
     * This is called when removing (deleting) a model.
     *
     * @param EloquentModel $model
     */
    public function forgetFieldKeys(EloquentModel $model)
    {

    }

    /**
     * Update the cached copy of a model.
     *
     * @param mixed $modelId
     *
     * @return EloquentModel|null
     */
    public function refreshById($modelId)
    {
        $model = parent::find($modelId);
        $this->rememberModel($model);

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
            $this->rememberModel($this->fresh($model));

            return true;
        }

        return false;
    }

    public function fresh(EloquentModel $model)
    {
        $model = parent::fresh($model);

        $this->rememberModel($model);

        return $model;
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
            $this->forgetById($model->getKey());
        }

        return $result;
    }

    public function incrementOrDecrement(EloquentModel $model, $column, $amount = 1)
    {
        parent::incrementOrDecrement($model, $column, $amount);

        return $this->refreshById($model->getKey());
    }

    /**
     * Store a model (or remember its lack of existence) in the cache.
     *
     * @param mixed $modelId
     * @param EloquentModel|null $model
     */
    protected function storeModelResultInCache($modelId, $model)
    {
        $cacheKey = $this->getCacheKey($modelId);

        $this->cache->forever($cacheKey, ($model ?: false));
    }

    /**
     * Return the unique string to use as the cache key for this model.
     * Default is the lowercase model class name.
     *
     * @param string $modelId
     *
     * @return string
     */
    protected function getCacheKey($modelId)
    {
        return strtolower($this->getModelClassWithoutNamespace()).':'.$modelId;
    }

    /**
     * Return the unique string to use to cache the primary key of a model looked up by a field.
     * e.g. For looking up users by username: user-username-key:anthony.
     * Return null to not cache.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return string|null
     */
    protected function getIdForFieldCacheKey($field, $value)
    {
        return strtolower($this->getModelClassWithoutNamespace().'-'.$field).'-id:'.strtolower($value);
    }
}

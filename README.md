# Laravel Repositories

The classes provided here allow you to use a repository pattern for CRUD operations on your Laravel models.

## Installation
```
composer require antriver/laravel-repositories
```

## Basic Usage

Create a class which extends `AbstractRepository`.

```
<?php

namespace App\Repositories;

use Antriver\LaravelRepositories\Base\AbstractRepository;
use App\Models\Post;

class PostRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Post::class;
    }
}
```

You now have a repository for Post models with the following methods:

### `find(int $modelId)`
Returns a single model by its primary key, or returns null.

### `findOrFail(int $modelId)`
Returns a single model by its primary key, or throws a `ModelNotFoundException`.

### `findMany(array $modelIds)`
Returns a collection of multiple models. The result will only contain the models that were found.

### `findOneBy(string $field, $value)`
Returns a single model where `field` matches `value`, or returns null.

### `findOneByOrFail(string $field, $value)`
Returns a single model where `field` matches `value`, or throws a `ModelNotFoundException`.

### `persist(Model $model)`
Save the given model to the database.

### `remove(Model $model)`
Delete the given model from the database.

### `incremenet(Model $model, $column, $amount)`
Increase the value in the given column by the amount specified.

(i.e. `UPDATE table SET column = column + 1`);

### `decrement(Model $model, $column, $amount)`
Decrease the value in the given column by the amount specified.

(i.e. `UPDATE table SET column = column - 1`);

## Caching Repository

If you extend `AbstractCachedRepository` instead of `AbstractRepository` you will have all the same functions,
however the following operations will look in a cache for the model first.
- **find** - Caches the model by its primary key.
- **findOrFail** - Caches the model by its primary key.
- **findMany** - Uses the same cache keys are `find` to locate each model.
- **findOneBy** - Caches the primary key of the model found with the matching value. Future calls will look up the matching ID for the value and then call `find`.
- **findOneByOrFail** - Caches the primary key of the model found with the matching value. Future calls will look up the matching ID for the value and then call `find`.

- **persist** - This will also store the model in the cache after it is saved to the database.
- **remove** - This will also remove the model from the cache after it is saved to the database.

The caching repository also has these additional methods:

### `forgetById(int $modelId)`
Forgets a cached copy of a model (does not delete it from a database).

### `forgetByModel(Model $model)`
Forgets a cached copy of a model (does not delete it from a database).

### `refreshById(int $modelId)`
Updates the cached copy of a model with the latest version found in the database.

## Soft Deletable Repository 

If your model supports soft deletes (`Illuminate\Database\Eloquent\SoftDeletes` trait), the find etc. methods above will
exclude soft deleted models from the results.

If you extend `AbstractSoftDeletableRepository` instead of `AbstractRepository` you will get these additional methods:

### `findWithTrashed(int $modelId)`
### `findWithTrashedOrFail(int $modelId)`
Returns a single model by primary key even if it has been soft-deleted.

### `findTrashed(int $modelId)`
### `findTrashedOrFail(int $modelId)`
Returns a model by primary key _only_ if it has been soft-deleted.

### `findOneByWithTrashed(string $field $value)`
### `findOneByWithTrashedOrFail(string $field, $value)`
Returns a single model where `field` matches `value` even if it has been soft-deleted.

### `findTrashedOneBy(string $field $value)`
### `findTrashedOneByOrFail(string $field, $value)`
Returns a single model where `field` matches `value` _only_ if it has been soft-deleted.

## Caching Soft Deletable Repository

This combines the functionality of `AbstractCachedRepository` and `AbstractSoftDeletableRepository`.

When using this, the cache of models will be populated with soft-deleted models too. This applies to the primary key cache and 
the field/value cache.
There is filtering inside the repository to return null/throw as appropriate if a trashed or not-trashed model is requested.




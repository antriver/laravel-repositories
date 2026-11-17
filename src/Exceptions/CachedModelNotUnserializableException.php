<?php

namespace Antriver\LaravelRepositories\Exceptions;

/**
 * Thrown when a cached model comes back from the cache as an incomplete class, because its class is not allowed
 * by the cache.serializable_classes config option or no longer exists.
 */
class CachedModelNotUnserializableException extends \RuntimeException
{
}

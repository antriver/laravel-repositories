<?php

namespace Tmd\LaravelRepositories\Base;

use Tmd\LaravelRepositories\Base\Traits\FindSoftDeletableModelsOrFailTrait;
use Tmd\LaravelRepositories\Base\Traits\FindSoftDeletableModelsTrait;
use Tmd\LaravelRepositories\Interfaces\SoftDeletableRepositoryInterface;

abstract class AbstractCachedSoftDeletableRepository extends AbstractCachedRepository implements SoftDeletableRepositoryInterface
{
    use FindSoftDeletableModelsTrait;
    use FindSoftDeletableModelsOrFailTrait;
}

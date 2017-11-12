<?php

namespace Tmd\LaravelRepositories\Base;

use Tmd\LaravelRepositories\Base\Traits\FindSoftDeletableModelsOrFailTrait;
use Tmd\LaravelRepositories\Base\Traits\QueryForSoftDeletableModelsTrait;
use Tmd\LaravelRepositories\Interfaces\SoftDeletableRepositoryInterface;

abstract class AbstractSoftDeletableRepository extends AbstractRepository implements SoftDeletableRepositoryInterface
{
    use QueryForSoftDeletableModelsTrait;
    use FindSoftDeletableModelsOrFailTrait;
}

<?php

namespace Antriver\LaravelRepositories\Base;

use Antriver\LaravelRepositories\Base\Traits\FindSoftDeletableModelsOrFailTrait;
use Antriver\LaravelRepositories\Base\Traits\QueryForSoftDeletableModelsTrait;
use Antriver\LaravelRepositories\Interfaces\SoftDeletableRepositoryInterface;

abstract class AbstractSoftDeletableRepository extends AbstractRepository implements SoftDeletableRepositoryInterface
{
    use QueryForSoftDeletableModelsTrait;
    use FindSoftDeletableModelsOrFailTrait;
}

<?php

namespace Tmd\LaravelRepositories\Tests\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Model as EloquentModel;

trait TestableRepositoryTrait
{
    /**
     * @var bool
     */
    public $changeCalled = false;

    /**
     * @var Closure|null
     */
    public $changed;

    /**
     * @var bool
     */
    public $deleteCalled = false;

    /**
     * @var Closure|null
     */
    public $deleted;

    /**
     * @var bool
     */
    public $insertCalled = false;

    /**
     * @var Closure|null
     */
    public $inserted;

    /**
     * @var bool
     */
    public $updateCalled = false;

    /**
     * @var Closure|null
     */
    public $updated;

    /**
     * @param EloquentModel $model
     * @param array|null $dirtyAttributes
     */
    protected function onChange(EloquentModel $model, array $dirtyAttributes = null)
    {
        $this->changeCalled = true;

        if ($this->changed instanceof Closure) {
            ($this->changed)($model, $dirtyAttributes);
        }
    }

    /**
     * @param EloquentModel $model
     */
    protected function onDelete(EloquentModel $model)
    {
        $this->deleteCalled = true;

        if ($this->deleted instanceof Closure) {
            ($this->deleted)($model);
        }
    }

    /**
     * @param EloquentModel $model
     */
    protected function onInsert(EloquentModel $model)
    {
        $this->insertCalled = true;

        if ($this->inserted instanceof Closure) {
            ($this->inserted)($model);
        }
    }

    /**
     * @param EloquentModel $model
     * @param array|null $dirtyAttributes
     */
    protected function onUpdate(EloquentModel $model, array $dirtyAttributes = null)
    {
        $this->updateCalled = true;

        if ($this->updated instanceof Closure) {
            ($this->updated)($model, $dirtyAttributes);
        }
    }
}

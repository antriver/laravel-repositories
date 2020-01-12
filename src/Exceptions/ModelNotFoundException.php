<?php

namespace Tmd\LaravelRepositories\Exceptions;

class ModelNotFoundException extends \Illuminate\Database\Eloquent\ModelNotFoundException
{
    /**
     * The field that was looked up.
     *
     * @var string
     */
    protected $field = 'id';

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function setField(string $field)
    {
        $this->field = $field;

        return $this;
    }
}

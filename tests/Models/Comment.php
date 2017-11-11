<?php

namespace Tmd\LaravelRepositories\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A comment is an example soft deletable model.
 */
class Comment extends Model
{
    use SoftDeletes;

    public $guarded = [];
}

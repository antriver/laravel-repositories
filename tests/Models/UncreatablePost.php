<?php

namespace Tmd\LaravelRepositories\Tests\Models;

class UncreatablePost extends Post
{
    public static function boot()
    {
        self::creating(
            function () {
                return false;
            }
        );
    }
}

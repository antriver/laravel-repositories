<?php

namespace Tmd\LaravelRepositories\Tests;

use Tmd\LaravelRepositories\Tests\Models\Post;

trait TestsWithPostsTrait
{
    public function setUp()
    {
        parent::setUp();

        \DB::delete('TRUNCATE TABLE posts');

        $this->models[1] = new Post(
            [
                'id' => 1,
                'text' => 'Model 1',
            ]
        );
        $this->models[1]->save();

        $this->models[2] = new Post(
            [
                'id' => 2,
                'text' => 'Model 2',
            ]
        );
        $this->models[2]->save();
    }

    public function tearDown()
    {
        \DB::delete('TRUNCATE TABLE posts');

        parent::tearDown();
    }

    /**
     * @return string
     */
    protected function getTestModelClass(): string
    {
        return Post::class;
    }

    protected function getModelNameString(): string
    {
        return 'post';
    }

    /**
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getTestModelInstance(array $attributes = [])
    {
        return new Post($attributes);
    }
}

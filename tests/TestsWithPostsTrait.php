<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Tests\Models\Post;

trait TestsWithPostsTrait
{
    public function setUp(): void
    {
        parent::setUp();

        \DB::delete('DELETE FROM posts');

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

    public function tearDown(): void
    {
        \DB::delete('DELETE FROM posts');

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

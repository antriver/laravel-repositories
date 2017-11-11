<?php

namespace Tmd\LaravelRepositories\Tests;

use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Models\Comment;
use Tmd\LaravelRepositories\Tests\Repositories\CommentRepository;

trait TestsWithCommentsTrait
{
    public function setUp()
    {
        parent::setUp();

        \DB::delete('TRUNCATE TABLE comments');

        $this->models[1] = new Comment(
            [
                'id' => 1,
                'text' => 'Model 1',
            ]
        );
        $this->models[1]->save();

        $this->models[2] = new Comment(
            [
                'id' => 2,
                'text' => 'Model 2',
            ]
        );
        $this->models[2]->save();

        $this->models[3] = new Comment(
            [
                'id' => 3,
                'text' => 'Model 3',
            ]
        );
        $this->models[3]->save();
        $this->models[3]->delete();
    }

    public function tearDown()
    {
        \DB::delete('TRUNCATE TABLE comments');

        parent::tearDown();
    }

    /**
     * @return AbstractRepository|CommentRepository
     */
    protected function createRepository()
    {
        return new CommentRepository();
    }

    protected function getTestModelClass(): string
    {
        return Comment::class;
    }

    protected function getModelNameString(): string
    {
        return 'comment';
    }

    protected function getTestModelInstance(array $attributes = [])
    {
        return new Comment($attributes);
    }
}

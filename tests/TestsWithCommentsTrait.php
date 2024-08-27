<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Base\AbstractRepository;
use Antriver\LaravelRepositories\Tests\Models\Comment;
use Antriver\LaravelRepositories\Tests\Repositories\CommentRepository;

trait TestsWithCommentsTrait
{
    public function setUp(): void
    {
        parent::setUp();

        \DB::statement('DROP TABLE IF EXISTS `comments`');
        \DB::statement(
            'CREATE TABLE `comments` (
              `id` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `text` VARCHAR(255) DEFAULT NULL,
              `views` INT(11) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME DEFAULT NULL,
              `deleted_at` DATETIME DEFAULT NULL
            )'
        );

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

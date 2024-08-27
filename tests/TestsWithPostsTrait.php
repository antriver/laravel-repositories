<?php

namespace Antriver\LaravelRepositories\Tests;

use Antriver\LaravelRepositories\Tests\Models\Post;

trait TestsWithPostsTrait
{
    public function setUp(): void
    {
        parent::setUp();

        \DB::statement('DROP TABLE IF EXISTS `posts`');
        \DB::statement(
            'CREATE TABLE `posts` (
              `id` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `text` VARCHAR(255) DEFAULT NULL,
              `views` INT(11) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME DEFAULT NULL
            )'
        );

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

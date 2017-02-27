<?php

namespace Tmd\LaravelRepositories\Tests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Orchestra\Testbench\TestCase;
use Tmd\LaravelRepositories\Base\AbstractRepository;
use Tmd\LaravelRepositories\Tests\Models\Post;
use Tmd\LaravelRepositories\Tests\Models\UncreatablePost;
use Tmd\LaravelRepositories\Tests\Repositories\PostRepository;

class AbstractRepositoryTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'repository-tests');
        $app['config']->set(
            'database.connections.repository-tests',
            [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'repository-tests',
                'username' => 'root',
                'password' => 'root',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ]
        );
    }

    /**
     * @return AbstractRepository
     */
    protected function getRepository()
    {
        return new PostRepository();
    }

    protected function getModelInstance()
    {
        $repository = $this->getRepository();
        $class = $repository->getModelClass();
        return new $class();
    }

    public function tearDown()
    {
        \DB::delete("DELETE FROM posts WHERE text LIKE 'Hello World%'");
    }

    /**
     * Test the tests can work first.
     */
    public function testBaseFind()
    {
        $post = Post::find(1);

        $this->assertInstanceOf(Post::class, $post);
    }

    public function testFindReturnsModel()
    {
        $repository = $this->getRepository();

        $post = $repository->find(1);

        $this->assertInstanceOf($repository->getModelClass(), $post);
        $this->assertSame('Post 1', $post->text);
    }

    public function testFindReturnsNull()
    {
        $repository = $this->getRepository();

        $post = $repository->find(4);

        $this->assertNull($post);
    }

    public function testFindOrFailReturnsModel()
    {
        $repository = $this->getRepository();

        $post = $repository->findOrFail(2);

        $this->assertInstanceOf($repository->getModelClass(), $post);
        $this->assertSame('Post 2', $post->text);
    }

    public function testFindOrFailThrowsException()
    {
        //$this->expectException(ModelNotFoundException::class);
        $repository = $this->getRepository();

        try {
            $post = $repository->findOrFail(4);
        } catch (ModelNotFoundException $e) {
        }

        $this->assertInstanceOf(ModelNotFoundException::class, $e);
        $this->assertSame("Tmd\\LaravelRepositories\\Tests\\Models\\Post with id 4 not found.", $e->getMessage());
    }

    public function testFindOneByReturnsModel()
    {
        $repository = $this->getRepository();

        $post = $repository->findOneBy('text', 'Post 2');

        $this->assertInstanceOf($repository->getModelClass(), $post);
        $this->assertSame('Post 2', $post->text);
        $this->assertSame(2, $post->id);
    }

    public function testFindOneByReturnsNull()
    {
        $repository = $this->getRepository();

        $post = $repository->findOneBy('text', 'Some Text');

        $this->assertNull($post);
    }

    public function testFindOneByOrFailReturnsModel()
    {
        $repository = $this->getRepository();

        $post = $repository->findOneByOrFail('text', 'Post 2');

        $this->assertInstanceOf($repository->getModelClass(), $post);
        $this->assertSame('Post 2', $post->text);
        $this->assertSame(2, $post->id);
    }

    public function testFindOneByOrFailThrowsException()
    {
        //$this->expectException(ModelNotFoundException::class);
        $repository = $this->getRepository();

        try {
            $post = $repository->findOneByOrFail('text', 'Something Missing');
        } catch (ModelNotFoundException $e) {
        }

        $this->assertInstanceOf(ModelNotFoundException::class, $e);
        $this->assertSame(
            "Tmd\\LaravelRepositories\\Tests\\Models\\Post with text Something Missing not found.",
            $e->getMessage()
        );
    }

    public function testAll()
    {
        $repository = $this->getRepository();

        $posts = $repository->all();

        $this->assertNotEmpty($posts);
        $this->assertContainsOnlyInstancesOf($repository->getModelClass(), $posts);
    }

    public function testIncrement()
    {
        $repository = $this->getRepository();

        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = Post::find(10);
        $oldViews = $post->views;

        $repository->increment($post, 'views');

        $post = Post::find(10);
        $this->assertSame($oldViews + 1, $post->views);
    }

    public function testIncrementMultiple()
    {
        $repository = $this->getRepository();

        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = Post::find(10);
        $oldViews = $post->views;

        $repository->increment($post, 'views', 20);

        $post = Post::find(10);
        $this->assertSame($oldViews + 20, $post->views);
    }

    public function testDecrement()
    {
        $repository = $this->getRepository();

        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = Post::find(10);
        $oldViews = $post->views;

        $repository->decrement($post, 'views');

        $post = Post::find(10);
        $this->assertSame($oldViews - 1, $post->views);
    }

    public function testDecrementMultiple()
    {
        $repository = $this->getRepository();

        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = Post::find(10);
        $oldViews = $post->views;

        $repository->decrement($post, 'views', 20);

        $post = Post::find(10);
        $this->assertSame($oldViews - 20, $post->views);
    }

    public function testPersist()
    {
        $repository = $this->getRepository();

        $text = 'Hello world '.uniqid();
        $post = new Post(['text' => $text]);

        $this->assertTrue($repository->persist($post));

        $postId = $post->id;

        $savedPost = Post::find($postId);

        $this->assertInstanceOf($repository->getModelClass(), $savedPost);
        $this->assertSame($postId, $post->id);
        $this->assertSame($text, $post->text);
    }

    public function testRemove()
    {
        $repository = $this->getRepository();

        $text = 'Hello world '.uniqid();
        $post = Post::create(['text' => $text]);
        /** @var Post $post */
        $post = $post->fresh();

        $this->assertTrue($repository->remove($post));

        $this->assertNull(Post::find($post->id));
    }

    public function testFresh()
    {
        $repository = $this->getRepository();

        $text = 'Hello world '.uniqid();
        $post = Post::create(['text' => $text]);

        $post = $repository->fresh($post);
        $this->assertInstanceOf($repository->getModelClass(), $post);
    }

    public function testUpdate()
    {
        $repository = $this->getRepository();

        $text = 'Hello world '.uniqid();
        $post = Post::create(['text' => $text]);
        /** @var Post $post */
        $post = $post->fresh();
        $postId = $post->id;

        $str = 'Hello world edited '.uniqid();
        $post->text = $str;
        $this->assertTrue($repository->persist($post));

        $post = Post::find($postId);
        $this->assertSame($str, $post->text);
    }

    public function testUpdateReturnsFalse()
    {
        $repository = $this->getRepository();

        $text = 'Hello world '.uniqid();
        $post = UncreatablePost::create(['text' => $text]);

        $this->assertFalse($repository->persist($post));
    }

    public function testOnInsert()
    {
        $repository = $this->getRepository();

        $insertedModel = null;
        $repository->inserted = function ($model) use (&$insertedModel) {
            $insertedModel = $model;
        };

        $changedModel = null;
        $changedAttributes = null;
        $repository->changed = function ($model, $dirtyAttributes) use (&$changedModel, &$changedAttributes) {
            $changedModel = $model;
            $changedAttributes = $dirtyAttributes;
        };

        $repository->updated = function ($model) {
            throw new \Exception("Should not receive onUpdate");
        };

        $text = 'Hello world '.uniqid();
        $post = new Post(['text' => $text]);

        $repository->persist($post);

        $this->assertInstanceOf($repository->getModelClass(), $insertedModel);
        $this->assertSame($post, $insertedModel);

        $this->assertInstanceOf($repository->getModelClass(), $changedModel);
        $this->assertSame($post, $changedModel);
        $this->assertSame(['text' => null], $changedAttributes);
    }

    public function testOnUpdate()
    {
        $oldText = 'Hello world '.uniqid();
        $post = new Post(['text' => $oldText]);

        $repository = $this->getRepository();

        $repository->persist($post);

        $repository->inserted = function ($model) {
            throw new \Exception("Should not receive onInsert");
        };

        $changedModel = null;
        $changedAttributes = null;
        $repository->changed = function ($model, $dirtyAttributes) use (&$changedModel, &$changedAttributes) {
            $changedModel = $model;
            $changedAttributes = $dirtyAttributes;
        };

        $updatedModel = null;
        $updatedAttributes = null;
        $repository->updated = function ($model, $dirtyAttributes) use (&$updatedModel, &$updatedAttributes) {
            $updatedModel = $model;
            $updatedAttributes = $dirtyAttributes;
        };

        $newText = 'Hello world '.uniqid();
        $post->text = $newText;

        $repository->persist($post);

        $this->assertInstanceOf($repository->getModelClass(), $updatedModel);
        $this->assertSame($post, $updatedModel);
        $this->assertSame(['text' => $oldText], $updatedAttributes);

        $this->assertInstanceOf($repository->getModelClass(), $changedModel);
        $this->assertSame($post, $changedModel);
        $this->assertSame(['text' => $oldText], $changedAttributes);
    }

    public function testOnDelete()
    {
        $repository = $this->getRepository();

        $oldText = 'Hello world '.uniqid();
        $post = new Post(['text' => $oldText]);

        $repository->persist($post);

        $deletedModel = null;
        $repository->deleted = function ($model) use (&$deletedModel) {
            $deletedModel = $model;
        };

        $changedModel = null;
        $changedAttributes = null;
        $repository->changed = function ($model, $dirtyAttributes) use (&$changedModel, &$changedAttributes) {
            $changedModel = $model;
            $changedAttributes = $dirtyAttributes;
        };

        $repository->remove($post);

        $this->assertInstanceOf($repository->getModelClass(), $deletedModel);
        $this->assertSame($post, $deletedModel);

        $this->assertInstanceOf($repository->getModelClass(), $changedModel);
        $this->assertSame($post, $changedModel);
        $this->assertNull($changedAttributes);
    }

    /*public function testGetModelClassWithoutNamespace()
    {
        $repository = $this->getRepository();

        $this->assertSame('Post', $repository->getModelClassWithoutNamespace());
    }*/
}

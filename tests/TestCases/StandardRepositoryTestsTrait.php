<?php

namespace Antriver\LaravelRepositories\Tests\TestCases;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Antriver\LaravelRepositories\Base\AbstractRepository;

trait StandardRepositoryTestsTrait
{
    /**
     * @var Model[]
     */
    protected $models;

    /**
     * @var AbstractRepository
     */
    protected $repository;

    /**
     * Test that the Laravel find() methods are working first.
     * If not something is wrong with the test config.
     */
    public function testBaseFind()
    {
        $post = $this->find(1);

        $this->assertInstanceOf($this->getTestModelClass(), $post);
    }

    public function testFindReturnsModel()
    {
        $post = $this->repository->find(1);

        $this->assertInstanceOf($this->repository->getModelClass(), $post);
        $this->assertSameModel($this->models[1], $post);
    }

    public function testFindReturnsNull()
    {
        $post = $this->repository->find(4);

        $this->assertNull($post);
    }

    public function testFindMany()
    {
        $expected = [1, 2];

        $actual = array_map(
            function ($result) {
                return $result->id;
            },
            $this->repository->findMany([1, 2])->all()
        );
        sort($actual);

        $this->assertSame($expected, $actual);
    }

    public function testFindManyReturnsEmpty()
    {
        $expected = [];

        $actual = array_map(
            function ($result) {
                return $result->id;
            },
            $this->repository->findMany([])->all()
        );
        sort($actual);

        $this->assertSame($expected, $actual);
    }

    public function testFindManyIgnoresMissing()
    {
        $expected = [2];

        $actual = array_map(
            function ($result) {
                return $result->id;
            },
            $this->repository->findMany([2, 3, 4])->all()
        );
        sort($actual);

        $this->assertSame($expected, $actual);
    }

    public function testFindReturnsNullForEmptyKey()
    {
        // $this->assertNull($this->repository->find(''));
        $this->assertNull($this->repository->find(0));
        // $this->assertNull($this->repository->find(false));
        // $this->assertNull($this->repository->find(null));
    }

    public function testFindOrFailReturnsModel()
    {
        $post = $this->repository->findOrFail(1);

        $this->assertInstanceOf($this->repository->getModelClass(), $post);
        $this->assertSameModel($this->models[1], $post);
    }

    public function testFindOrFailThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findOrFail(500);
    }

    public function testFindOneByReturnsModel()
    {
        $post = $this->repository->findOneBy('text', 'Model 2');

        $this->assertInstanceOf($this->repository->getModelClass(), $post);
        $this->assertSameModel($this->models[2], $post);
    }

    public function testFindOneByReturnsNull()
    {
        $post = $this->repository->findOneBy('text', 'Some Text');

        $this->assertNull($post);
    }

    public function testFindOneByThrowsExceptionWithNoFieldName()
    {
        $this->expectExceptionMessage("A field must be specified.");
        $this->expectException(\Exception::class);

        $this->repository->findOneBy('', 'Some Text');
    }

    public function testFindOneByReturnsNullWithNoValue()
    {
        $this->assertNull($this->repository->findOneBy('text', ''));
    }

    public function testFindOneByOrFailReturnsModel()
    {
        $post = $this->repository->findOneByOrFail('text', 'Model 1');

        $this->assertInstanceOf($this->repository->getModelClass(), $post);
        $this->assertSameModel($this->models[1], $post);
    }

    public function testFindOneByOrFailThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findOneByOrFail('text', 'Something Missing');
    }

    public function testAll()
    {
        $posts = $this->repository->all();

        $this->assertNotEmpty($posts);
        $this->assertCount(2, $posts);
        $this->assertSameModel($this->models[1], $posts[0]);
        $this->assertSameModel($this->models[2], $posts[1]);
    }

    public function testIncrementByOne()
    {
        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = $this->find(1);
        $this->assertSame($post->views, 0);

        $this->repository->increment($post, 'views');
        $post = $this->find(1);
        $this->assertSame(1, $post->views);

        $this->repository->increment($post, 'views');
        $post = $this->find(1);
        $this->assertSame(2, $post->views);
    }

    public function testIncrementByMany()
    {
        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = $this->find(1);
        $this->assertSame($post->views, 0);

        $this->repository->increment($post, 'views', 20);
        $post = $this->find(1);
        $this->assertSame(20, $post->views);

        $this->repository->increment($post, 'views', 20);
        $post = $this->find(1);
        $this->assertSame(40, $post->views);
    }

    public function testDecrementByOne()
    {
        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = $this->find(1);
        $this->assertSame($post->views, 0);

        $this->repository->decrement($post, 'views');
        $post = $this->find(1);
        $this->assertSame(-1, $post->views);

        $this->repository->decrement($post, 'views');
        $post = $this->find(1);
        $this->assertSame(-2, $post->views);
    }

    public function testDecrementMultiple()
    {
        // Get it from the raw DB because we're not trying to test the find methods here.
        $post = $this->find(1);
        $this->assertSame($post->views, 0);

        $this->repository->decrement($post, 'views', 20);
        $post = $this->find(1);
        $this->assertSame(-20, $post->views);

        $this->repository->decrement($post, 'views', 20);
        $post = $this->find(1);
        $this->assertSame(-40, $post->views);
    }

    public function testPersist()
    {
        $text = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $text]);

        $this->assertTrue($this->repository->persist($post));

        $savedPost = $this->find($post->id);
        $this->assertInstanceOf($this->getTestModelClass(), $savedPost);
        $this->assertSameModel($post, $savedPost);
    }

    public function testRemove()
    {
        $text = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $text]);
        $post->save();

        $this->assertInstanceOf($this->getTestModelClass(), $this->find($post->id));

        $this->assertTrue($this->repository->remove($post));

        $this->assertNull($this->find($post->id));
    }

    public function testFresh()
    {
        $text = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $text]);
        $post->save();

        $post->text = 'modified';

        $post = $this->repository->fresh($post);

        $this->assertInstanceOf($this->repository->getModelClass(), $post);
        $this->assertSame($text, $post->text);
    }

    public function testUpdate()
    {
        $text = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $text]);
        $post->save();

        $str = 'Hello world edited '.uniqid();
        $post->text = $str;
        $this->assertTrue($this->repository->persist($post));

        $post = $this->find($post->id);
        $this->assertSame($str, $post->text);
    }

    public function testOnInsert()
    {
        $text = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $text]);

        $this->repository->changed = function ($model, $dirtyAttributes) use ($post) {
            $this->assertInstanceOf($this->getTestModelClass(), $model);
            $this->assertSame($post, $model);
            $this->assertSame(
                [
                    // Text attribute has been modified. Previous value was null.
                    'text' => null,
                ],
                $dirtyAttributes
            );
        };

        $this->repository->deleted = function ($model) {
            throw new \Exception("Should not receive onDelete");
        };

        $this->repository->inserted = function ($model) use ($post) {
            $this->assertInstanceOf($this->getTestModelClass(), $model);
            $this->assertSame($post, $model);
        };

        $this->repository->updated = function ($model) {
            throw new \Exception("Should not receive onUpdate");
        };

        $this->repository->persist($post);

        $this->assertTrue($this->repository->changeCalled);
        $this->assertFalse($this->repository->deleteCalled);
        $this->assertTrue($this->repository->insertCalled);
        $this->assertFalse($this->repository->updateCalled);
    }

    public function testOnUpdate()
    {
        $oldText = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $oldText]);
        $post->save();

        $newText = 'Goodbye world '.uniqid();
        $post->text = $newText;

        $this->repository->changed = function ($model, $dirtyAttributes) use ($post, $oldText) {
            $this->assertInstanceOf($this->getTestModelClass(), $model);
            $this->assertSame($post, $model);
            $this->assertSame(
                [
                    // Text attribute has been modified. Previous value was $oldText.
                    'text' => $oldText,
                ],
                $dirtyAttributes
            );
        };

        $this->repository->deleted = function ($model) {
            throw new \Exception("Should not receive onDelete");
        };

        $this->repository->inserted = function ($model) {
            throw new \Exception("Should not receive onInsert");
        };

        $this->repository->updated = function ($model, $dirtyAttributes) use ($post, $newText, $oldText) {
            $this->assertInstanceOf($this->getTestModelClass(), $model);
            $this->assertSame($post, $model);
            $this->assertSame($newText, $post->text);
            $this->assertSame(
                [
                    // Text attribute has been modified. Previous value was $oldText.
                    'text' => $oldText,
                ],
                $dirtyAttributes
            );
        };

        $this->repository->persist($post);

        $this->assertTrue($this->repository->changeCalled);
        $this->assertFalse($this->repository->deleteCalled);
        $this->assertFalse($this->repository->insertCalled);
        $this->assertTrue($this->repository->updateCalled);
    }

    public function testOnDelete()
    {
        $oldText = 'Hello world '.uniqid();
        $post = $this->getTestModelInstance(['text' => $oldText]);
        $post->save();

        $this->repository->changed = function ($model, $dirtyAttributes) use ($post, $oldText) {
            $this->assertInstanceOf($this->getTestModelClass(), $model);
            $this->assertSame($post, $model);
            $this->assertSame($oldText, $post->text);
            // dirtyAttributes are empty when deleting.
            $this->assertSame([], $dirtyAttributes);
        };

        $this->repository->deleted = function ($model) use ($post) {
            $this->assertInstanceOf($this->getTestModelClass(), $model);
            $this->assertSame($post, $model);
        };

        $this->repository->inserted = function ($model) {
            throw new \Exception("Should not receive onInsert");
        };

        $this->repository->updated = function ($model, $dirtyAttributes) {
            throw new \Exception("Should not receive onUpdate");
        };

        $this->repository->remove($post);

        $this->assertTrue($this->repository->changeCalled);
        $this->assertTrue($this->repository->deleteCalled);
        $this->assertFalse($this->repository->insertCalled);
        $this->assertFalse($this->repository->updateCalled);
    }
}

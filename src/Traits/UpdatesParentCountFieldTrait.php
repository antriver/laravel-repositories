<?php

namespace Tmd\LaravelRepositories\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRepositories\Base\AbstractRepository;

trait UpdatesParentCountFieldTrait
{
    /**
     * Helper to update a denormalised count field. For example, posts with a commentCount field.
     * You would call this updateCount method from the Comment repository when a comment's postId changes.
     * It will decrement the commentCount on the previous post (found via the old postId value), if applicable,
     * and incrememnt the commentCount on the new post (found via the new postId value), if applicable.
     *
     * @param EloquentModel $model A comment in the example.
     * @param string $valueField 'postId' in the example.
     * @param string $countField 'commentCount' in the example.
     * @param mixed $oldValue The old value of the $valueValue
     * @param AbstractRepository $repository A PostRepository instance in the example.
     *
     * @return bool
     */
    protected function updateParentCountField(
        EloquentModel $model,
        string $valueField,
        string $countField,
        $oldValue,
        AbstractRepository $repository
    ) {
        $newValue = $model->getAttribute($valueField);

        if ($oldValue == $newValue) {
            return false;
        }

        if ($oldValue) {
            $old = $repository->find($oldValue);
            $repository->decrement($old, $countField);
        }

        if ($newValue) {
            $new = $repository->find($newValue);
            $repository->increment($new, $countField);
        }
    }
}

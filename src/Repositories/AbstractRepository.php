<?php

declare(strict_types=1);

namespace Velor\Images\Repositories;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @template TModel of AbstractModel
 */
abstract class AbstractRepository
{
    /**
     * @var class-string<TModel>
     */
    protected string $model;

    /**
     * @return EloquentBuilder<TModel>
     */
    protected function query(): EloquentBuilder
    {
        $model = $this->model;
        /** @var EloquentBuilder<TModel> $query */
        $query = $model::query();

        return $query;
    }
}

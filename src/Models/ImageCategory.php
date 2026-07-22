<?php

declare(strict_types=1);

namespace Velor\Images\Models;

use App\Concerns\Models\UsesAudit;
use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kyslik\ColumnSortable\Sortable;
use Velor\Images\Database\Factories\ImageCategoryFactory;

/**
 * @property string $name
 *
 * @mixin \Eloquent
 */
class ImageCategory extends AbstractModel
{
    /** @use HasFactory<ImageCategoryFactory> */
    use HasFactory;
    use HasUuids;
    use Sortable;
    use UsesAudit;

    /**
     * @var array<int, string>
     */
    public array $sortable = [
        'name',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<int, string>
     */
    protected array $audit = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Image, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * @return array{
     *     created_at: 'datetime',
     *     updated_at: 'datetime',
     * }
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ImageCategoryFactory
    {
        return ImageCategoryFactory::new();
    }
}

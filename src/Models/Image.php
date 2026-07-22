<?php

declare(strict_types=1);

namespace Velor\Images\Models;

use App\Concerns\Models\UsesAudit;
use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kyslik\ColumnSortable\Sortable;
use Velor\Images\Database\Factories\ImageFactory;

/**
 * @property string $filename
 * @property string $name
 * @property string|null $image_category_id
 * @property int $sort_order
 * @property string|null $description
 * @property array<string, mixed>|null $meta_data
 * @property string $category_name
 * @property array<string, mixed> $original_meta_data
 *
 * @mixin \Eloquent
 */
class Image extends AbstractModel
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;
    use HasUuids;
    use Sortable;
    use UsesAudit;

    /**
     * @var array<int, string>
     */
    public array $sortable = [
        'name',
        'filename',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<int, string>
     */
    protected array $audit = [
        'filename',
        'name',
        'image_category_id',
        'sort_order',
        'description',
        'meta_data',
    ];

    protected $fillable = [
        'filename',
        'name',
        'image_category_id',
        'sort_order',
        'description',
        'meta_data',
    ];

    public function getCategoryNameAttribute(): string
    {
        $category = $this->category;

        if (! $category instanceof ImageCategory) {
            return 'Common';
        }

        return $category->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOriginalMetaDataAttribute(): array
    {
        $original = $this->metaData()['original'] ?? [];

        return is_array($original) ? $original : [];
    }

    /**
     * @return BelongsTo<ImageCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ImageCategory::class, 'image_category_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function metaData(): array
    {
        $metaData = $this->getAttribute('meta_data');

        return is_array($metaData) ? $metaData : [];
    }

    /**
     * @param array<string, mixed> $metaData
     */
    public function setMetaData(array $metaData): void
    {
        $this->setAttribute('meta_data', $metaData);
    }

    /**
     * @return array{
     *     meta_data: 'array',
     *     sort_order: 'int',
     *     created_at: 'datetime',
     *     updated_at: 'datetime',
     * }
     */
    protected function casts(): array
    {
        return [
            'meta_data'  => 'array',
            'sort_order' => 'int',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ImageFactory
    {
        return ImageFactory::new();
    }
}

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->foreignUuid('image_category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->nullable();
        });

        DB::statement(<<<'SQL'
            UPDATE images
            SET sort_order = ranked.sort_order
            FROM (
                SELECT id, row_number() OVER (ORDER BY created_at, id) AS sort_order
                FROM images
            ) AS ranked
            WHERE images.id = ranked.id
        SQL);

        Schema::table('images', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->nullable(false)->change();
            $table->dropColumn('category');
        });

        DB::statement('CREATE UNIQUE INDEX images_category_sort_order_unique ON images (image_category_id, sort_order) WHERE image_category_id IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX images_common_sort_order_unique ON images (sort_order) WHERE image_category_id IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS images_category_sort_order_unique');
        DB::statement('DROP INDEX IF EXISTS images_common_sort_order_unique');

        Schema::table('images', function (Blueprint $table): void {
            $table->string('category')->nullable()->index();
            $table->dropConstrainedForeignId('image_category_id');
            $table->dropColumn('sort_order');
        });
    }
};

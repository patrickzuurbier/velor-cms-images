<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('filename');
            $table->string('name');
            $table->foreignUuid('image_category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order');
            $table->json('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        Schema::getConnection()->statement('CREATE UNIQUE INDEX images_category_sort_order_unique ON images (image_category_id, sort_order) WHERE image_category_id IS NOT NULL');
        Schema::getConnection()->statement('CREATE UNIQUE INDEX images_common_sort_order_unique ON images (sort_order) WHERE image_category_id IS NULL');
    }
};

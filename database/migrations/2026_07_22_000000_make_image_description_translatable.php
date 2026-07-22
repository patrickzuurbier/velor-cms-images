<?php

declare(strict_types=1);

use App\Enums\LocaleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->json('description_translations')->nullable();
        });

        DB::statement(sprintf(
            <<<'SQL'
                UPDATE images
                SET description_translations = json_build_object(%s)::json
                WHERE description IS NOT NULL AND description <> ''
            SQL,
            implode(', ', $this->translationSqlArguments()),
        ));

        Schema::table('images', function (Blueprint $table): void {
            $table->dropColumn('description');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->renameColumn('description_translations', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table): void {
            $table->text('description_text')->nullable();
        });

        DB::statement(sprintf(
            <<<'SQL'
                UPDATE images
                SET description_text = COALESCE(%s)
                WHERE description IS NOT NULL
            SQL,
            implode(', ', $this->descriptionSqlExpressions()),
        ));

        Schema::table('images', function (Blueprint $table): void {
            $table->dropColumn('description');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->renameColumn('description_text', 'description');
        });
    }

    /**
     * @return array<int, string>
     */
    protected function translationSqlArguments(): array
    {
        $arguments = [];

        foreach (LocaleEnum::cases() as $locale) {
            $arguments[] = sprintf("'%s'", $locale->value);
            $arguments[] = 'description';
        }

        return $arguments;
    }

    /**
     * @return array<int, string>
     */
    protected function descriptionSqlExpressions(): array
    {
        return array_map(
            fn (LocaleEnum $locale): string => sprintf("description->>'%s'", $locale->value),
            LocaleEnum::cases(),
        );
    }
};

<?php

declare(strict_types=1);

namespace Velor\Images\Console\Commands;

use Velor\Images\Services\Contracts\ImageRegenerationServiceInterface;
use Illuminate\Console\Command;

class RegenerateImagesCommand extends Command
{
    protected $signature = 'velor:images:regenerate
        {--chunk=100 : The number of images to process per chunk}';

    protected $description = 'Regenerate all configured image formats from stored originals';

    public function handle(ImageRegenerationServiceInterface $imageRegenerationService): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $count = $imageRegenerationService->regenerate($chunkSize);

        $this->info("Regenerated formats for {$count} images.");

        return self::SUCCESS;
    }
}

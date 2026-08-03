<?php

declare(strict_types=1);

namespace Velor\Images\Console\Commands;

use Illuminate\Console\Command;
use Velor\Images\Services\Contracts\ImageRecoveryServiceInterface;

class RecoverImagesCommand extends Command
{
    protected $signature = 'velor:images:recover
        {--dry-run : Report recoverable images without inserting database rows}';

    protected $description = 'Recover missing image records from originals stored on S3';

    public function handle(ImageRecoveryServiceInterface $imageRecoveryService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $count = $imageRecoveryService->recover(
            $dryRun,
            fn (string $reason) => $this->warn("Skipped {$reason}"),
        );

        if ($dryRun) {
            $this->info("Found {$count} images that can be recovered. No database rows were inserted.");
        } else {
            $this->info("Recovered {$count} images from S3.");
        }

        return self::SUCCESS;
    }
}

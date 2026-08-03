<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

interface ImageRecoveryServiceInterface
{
    /**
     * @param  callable(string): void|null  $onSkipped
     */
    public function recover(bool $dryRun = false, ?callable $onSkipped = null): int;
}

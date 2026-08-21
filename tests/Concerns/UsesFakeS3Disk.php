<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

trait UsesFakeS3Disk
{
    protected function fakeS3Disk(): FilesystemContract
    {
        $root = $this->getApplication()->storagePath('framework/testing/disks/s3');

        $filesystem = $this->getApplication()->make(Filesystem::class);
        $filesystem->ensureDirectoryExists($root);
        $filesystem->cleanDirectory($root);

        $manager = $this->filesystemManager();
        $manager->set('s3', $manager->createLocalDriver([
            'root' => $root,
        ]));

        return $this->s3Disk();
    }

    protected function s3Disk(): FilesystemContract
    {
        return $this->filesystemManager()->disk('s3');
    }

    protected function filesystemManager(): FilesystemManager
    {
        return $this->getApplication()->make(FilesystemManager::class);
    }
}

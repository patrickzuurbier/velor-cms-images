<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Unit\Images;

use Velor\Images\Models\Image;
use Velor\Images\Services\ImageDisplayFormatResolver;
use Tests\Unit\AbstractUnitTestCase;

class ImageDisplayFormatResolverTest extends AbstractUnitTestCase
{
    public function test_index_format_is_thumbnail(): void
    {
        $resolver = new ImageDisplayFormatResolver();

        $this->assertSame('thumbnail', $resolver->index(new Image()));
    }

    public function test_cms_format_is_used_when_available(): void
    {
        $resolver = new ImageDisplayFormatResolver();
        $image = new Image([
            'meta_data' => [
                'formats' => [
                    'cms'  => [],
                    'full' => [],
                ],
            ],
        ]);

        $this->assertSame('cms', $resolver->cms($image));
    }

    public function test_cms_format_falls_back_to_full_when_unavailable(): void
    {
        $resolver = new ImageDisplayFormatResolver();
        $image = new Image([
            'meta_data' => [
                'formats' => [
                    'full' => [],
                ],
            ],
        ]);

        $this->assertSame('full', $resolver->cms($image));
    }
}

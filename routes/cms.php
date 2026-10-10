<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Velor\Images\Http\Controllers\ImageController;
use Velor\Images\Http\Controllers\ImagePickerController;

/**
 * @var Router $router
 */
$router->get('images/picker', ImagePickerController::class)->name('images.picker');
$router->resource('images', ImageController::class);

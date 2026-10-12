<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Velor\Images\Http\Controllers\ImageController;
use Velor\Images\Http\Controllers\ImagePickerController;
use Velor\Images\Http\Controllers\ImagePickerUploadController;

/**
 * @var Router $router
 */
$router->get('images/picker', ImagePickerController::class)->name('images.picker');
$router->post('images/picker', ImagePickerUploadController::class)->name('images.picker.upload');
$router->resource('images', ImageController::class);

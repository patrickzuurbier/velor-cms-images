<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Velor\Images\Http\Controllers\ImageCategoryController;
use Velor\Images\Http\Controllers\ImageController;
use Velor\Images\Http\Controllers\ImagePickerController;

/**
 * @var Router $router
 */
$router->get('/images/picker', ImagePickerController::class)->name('images.picker');
$router->post('/images/reorder', [ImageController::class, 'reorder'])->name('images.reorder');
$router->resources(['images' => ImageController::class]);
$router->resources(['image-categories' => ImageCategoryController::class]);

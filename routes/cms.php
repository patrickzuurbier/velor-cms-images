<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Velor\Images\Http\Controllers\ImageController;

/**
 * @var Router $router
 */
$router->resource('images', ImageController::class);

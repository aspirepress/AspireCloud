<?php

declare(strict_types=1);

use AspirePress\Cdn\V1\PluginCheck\Handlers\CatchAllHandler;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * FastRoute route configuration
 *
 * @see https://github.com/nikic/FastRoute
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', AspirePress\Cdn\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', AspirePress\Cdn\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/{id:\d+}', AspirePress\Cdn\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/{id:\d+}', AspirePress\Cdn\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/{id:\d+}', AspirePress\Cdn\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', AspirePress\Cdn\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 */

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->route('/{path:.*}', CatchAllHandler::class, ['GET', 'POST'], 'app.home');
};

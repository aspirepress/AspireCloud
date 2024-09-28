<?php

declare(strict_types=1);

namespace AspirePress\Cdn\V1\PluginCheck\Handlers;

use AspirePress\Cdn\Data\Repositories\PluginRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PluginCheckHandler implements RequestHandlerInterface
{
    public function __construct(
        private PluginRepository $pluginVersionRepository
    )
    {
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        return new JsonResponse($parsedBody);
    }
}
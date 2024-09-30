<?php

declare(strict_types=1);

namespace AspirePress\Cdn\V1\CatchAll\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CatchAllHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestData = $request->getParsedBody();

        $ua          = $request->getHeader('User-Agent');
        $path        = $request->getUri()->getPath();
        $queryParams = $request->getQueryParams();

        try {
            $guzzle   = new Client(['base_uri' => 'https://api.wordpress.org']);
            $response = $guzzle->request(
                $request->getMethod(),
                $path,
                [
                    'query'       => $queryParams,
                    'form_params' => $requestData,
                    'headers'     => ['User-Agent' => $ua[0], 'Accept' => '*/*'],
                ]
            );
        } catch (ClientException $e) {
            if (method_exists($e, 'getResponse') && $e->getResponse() instanceof ResponseInterface) {
                $statusCode = $e->getResponse()->getStatusCode();
            } else {
                $statusCode = 500;
            }
            return new EmptyResponse($statusCode);
        }

        $contentType = $response->getHeader('Content-Type');
        $statusCode  = $response->getStatusCode();

        return new TextResponse($response->getBody()->getContents(), $statusCode, ['Content-Type' => $contentType]);
    }
}

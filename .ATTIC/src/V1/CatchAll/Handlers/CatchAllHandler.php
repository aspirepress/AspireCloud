<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\V1\CatchAll\Handlers;

use Aura\Sql\ExtendedPdoInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class CatchAllHandler implements RequestHandlerInterface
{
    public function __construct(private ExtendedPdoInterface $pdo)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestData = $request->getParsedBody();

        $ua          = $request->getHeader('User-Agent');
        $path        = $request->getUri()->getPath();
        $queryParams = $request->getQueryParams();

        if ($path === '/') {
            return new EmptyResponse(200);
        }

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

        $content = $response->getBody()->getContents();
        $this->saveData($request, $response, $content);
        return new TextResponse($content, $statusCode, ['Content-Type' => $contentType]);
    }

    private function saveData(ServerRequestInterface $request, ResponseInterface $response, string $content): void
    {
        $this->pdo->perform(
            'INSERT INTO request_data (id, request_path, request_query_params, request_body, request_headers, response_code, response_body, response_headers, created_at) VALUES (:id, :request_path, :request_query_params, :request_body, :request_headers, :response_code, :response_body, :response_headers, NOW())',
            [
                'id'                   => Uuid::uuid7()->toString(),
                'request_path'         => $request->getUri()->getPath(),
                'request_query_params' => json_encode($request->getQueryParams()),
                'request_body'         => json_encode($request->getParsedBody()),
                'request_headers'      => json_encode($request->getHeaders()),
                'response_code'        => $response->getStatusCode(),
                'response_body'        => $content,
                'response_headers'     => json_encode($response->getHeaders()),
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class LoggingListener
{
    private const LOG_FORMAT = '%d [%s] %s: %s';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->logger->error(
            sprintf(
                self::LOG_FORMAT,
                $response->getStatusCode(),
                $request->getMethod(),
                (string) $request->getUri(),
                (string) $error
            )
        );
    }
}

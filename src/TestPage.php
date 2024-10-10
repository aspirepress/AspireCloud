<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestPage implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse(
            $this->templateRenderer->render('app::sample', ['statement' => 'Hello world!'])
        );
    }
}

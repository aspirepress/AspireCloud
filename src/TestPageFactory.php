<?php

namespace App;

use Laminas\ServiceManager\ServiceManager;
use Mezzio\Template\TemplateRendererInterface;

class TestPageFactory
{
    public function __invoke(ServiceManager $serviceManager) : TestPage
    {
        $templateRenderer = $serviceManager->get(TemplateRendererInterface::class);
        return new TestPage($templateRenderer);
    }
}

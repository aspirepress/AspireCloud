<?php


namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    /** @var list<class-string<Facade>> */
    protected array $fakeFacades = [
        Http::class,
        Mail::class,
        Queue::class,
        Storage::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        foreach ($this->fakeFacades as $facade) {
            $facade::fake();
        }
    }
}

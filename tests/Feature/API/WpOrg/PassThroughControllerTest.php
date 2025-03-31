<?php

use App\Http\Controllers\PassThroughController;

it('should throw an exception for unexpected pass-through requests', function () {
    $this->get('/events/1.0')->assertServerError();
});

it('should pass through when expecting to be hit', function () {
    Http::fake();   // should already be, but make sure
    PassThroughController::$expectsHit = true;
    $this->get('/events/1.0')->assertOk();  // response is still mocked, so it won't tell us anything more
});

// TODO: write test for real auth token

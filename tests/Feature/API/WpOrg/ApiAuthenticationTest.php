<?php
declare(strict_types=1);

it('should return 401 when an invalid auth token is present', function () {
    $this
        ->get('/plugins/info/1.1?action=query_plugins', ['Authorization' => 'Bearer invalid-token'])
        ->assertUnauthorized();
});

// TODO: write test for real auth token

<?php

it('should return 200 when no auth token is present', function () {
    $response = $this->getjson('/plugins/info/1.1?action=query_plugins');
    $response->assertStatus(200);
});

it('should return 401 when an invalid auth token is present', function () {
    $response = $this->getjson('/plugins/info/1.1?action=query_plugins', ['Authorization' => 'Bearer invalid-token']);
    $response->assertStatus(401);
});

// TODO: write test for real auth token

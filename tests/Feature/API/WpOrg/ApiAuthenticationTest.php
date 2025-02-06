<?php

it('should return 200 when no auth token is present', function () {
    $response = $this->getjson('/plugins/info/1.1?action=query_plugins');
    $response->assertStatus(200);
});

// TODO: write tests for real and and bad auth tokens

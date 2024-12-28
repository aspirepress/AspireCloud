<?php

it('should return 401 if the API authentication is enable', function () {
    $response = $this->getjson('/plugins/info/1.1?action=query_plugins');
    $response->assertStatus(401);
})->skip(fn() => !config('app.aspirecloud.api_authentication_enable'), 'API authentication is disabled');

it('should return 200 if the API authentication is disable', function () {
    $response = $this->getjson('/plugins/info/1.1?action=query_plugins');
    $response->assertStatus(200);
})->skip(fn() => config('app.aspirecloud.api_authentication_enable'), 'API authentication is enabled');

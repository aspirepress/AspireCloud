<?php

it('browses happy', function () {
    $response = makeApiRequest('GET', '/core/browse-happy/1.1');
    $response->assertStatus(200);
});

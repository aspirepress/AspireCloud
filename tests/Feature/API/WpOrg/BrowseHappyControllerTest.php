<?php

it('browses happy', function () {
    $this->get('/core/browse-happy/1.1')->assertStatus(200);
});

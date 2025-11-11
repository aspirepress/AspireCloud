<?php
declare(strict_types=1);

it('browses happy', function () {
    $this->get('/core/browse-happy/1.1')->assertStatus(200);
});

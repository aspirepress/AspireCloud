<?php

it('shows importer list', function () {
    $this
        ->get('/core/importers/1.1')
        ->assertStatus(200)
        ->assertJsonStructure([
            'importers' => ['*' => ['description', 'importer-id', 'name', 'plugin-slug']],
            'translated',
        ])
        ->assertJsonCount(8, 'importers');
});

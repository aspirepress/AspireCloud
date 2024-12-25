<?php

use App\Models\User;

test('new users have User role', function () {
    $user = User::factory()->create();
    expect($user->roles->first()->name)->toBe('User');
});

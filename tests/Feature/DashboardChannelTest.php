<?php

use App\Models\User;

beforeEach(function () {
    config()->set('broadcasting.default', 'reverb');
    require base_path('routes/channels.php');
});

test('authenticated users can authorize the private dashboard channel', function () {
    $this->actingAs(User::factory()->create())->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-dashboard',
    ])->assertSuccessful();
});

test('guests cannot authorize the private dashboard channel', function () {
    $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-dashboard',
    ])->assertForbidden();
});

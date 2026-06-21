<?php

use App\Models\User;

beforeEach(function () {
    config()->set('broadcasting.default',
        'reverb');
    require base_path('routes/channels.php');
});

test('authenticated users authorize the presence
  channel and expose their data', function () {
    $user = User::factory()->create(['name' => 'Jan Kowalski']);

    $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'presence-dashboard',
    ]);

    $response->assertSuccessful();
    expect($response->json('channel_data'))->toContain('Jan Kowalski');
});

test('guests cannot authorize the presence
  channel', function () {
    $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'presence-dashboard',
    ])->assertForbidden();
});

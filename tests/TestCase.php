<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function signInMember(?\App\Models\User $user = null): \App\Models\User
    {
        $user ??= \App\Models\User::factory()->create();
        \App\Models\Subscription::create([
            'user_id' => $user->id, 'game_id' => null, 'is_bundle' => true,
            'full_name' => $user->name, 'email' => $user->email, 'phone' => '01012345678',
            'receipt_image' => 'receipts/test.png', 'status' => 'approved',
        ]);
        $this->actingAs($user);
        return $user;
    }
}

<?php

use App\Models\User;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Pengujian Fitur Notifikasi (Notification Feature Tests)
|--------------------------------------------------------------------------
|
| Dokumen pengujian ini memverifikasi endpoint notifikasi pada KaryaOne.
| Pengujian mencakup autentikasi, pengambilan data notifikasi,
| penandaan notifikasi tunggal sebagai telah dibaca, dan penandaan semua
| notifikasi sebagai telah dibaca.
|
*/

// =========================================================================
// 1. PENGUJIAN AUTENTIKASI (Authentication Tests)
// =========================================================================

test('unauthenticated user cannot access notifications list', function () {
    $response = $this->getJson(route('notifications.index'));
    $response->assertStatus(401);
});

test('unauthenticated user cannot mark notification as read', function () {
    $response = $this->postJson(route('notifications.read', ['id' => Str::uuid()->toString()]));
    $response->assertStatus(401);
});

test('unauthenticated user cannot mark all notifications as read', function () {
    $response = $this->postJson(route('notifications.read-all'));
    $response->assertStatus(401);
});

// =========================================================================
// 2. PENGAMBILAN DATA NOTIFIKASI (Fetching Notifications)
// =========================================================================

test('authenticated user can fetch their notifications', function () {
    $user = User::factory()->create();

    // Buat 2 notifikasi dummy di database untuk user ini dengan created_at berbeda
    $notification1 = $user->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\TestNotification',
        'data' => [
            'type' => 'test',
            'message' => 'Notification 1 message',
            'url' => '/test-url-1',
        ],
        'read_at' => null,
    ]);
    $notification1->created_at = now()->subMinutes(5);
    $notification1->save();

    $notification2 = $user->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\TestNotification',
        'data' => [
            'type' => 'test2',
            'message' => 'Notification 2 message',
            'url' => '/test-url-2',
        ],
        'read_at' => null,
    ]);
    $notification2->created_at = now();
    $notification2->save();

    $response = $this->actingAs($user)->getJson(route('notifications.index'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'notifications' => [
                '*' => [
                    'id',
                    'data',
                    'read_at',
                    'created_at',
                ]
            ],
            'unread_count'
        ])
        ->assertJsonFragment([
            'unread_count' => 2,
        ]);

    $data = $response->json('notifications');
    expect($data)->toHaveCount(2);
    expect($data[0]['id'])->toBe($notification2->id); // Sesuai dengan urutan latest()
});

test('user can only see their own notifications', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Berikan notifikasi hanya kepada User B
    $userB->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\TestNotification',
        'data' => [
            'message' => 'Notification for User B',
        ],
        'read_at' => null,
    ]);

    // Login sebagai User A dan panggil GET /notifications
    $response = $this->actingAs($userA)->getJson(route('notifications.index'));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'unread_count' => 0,
        ]);

    expect($response->json('notifications'))->toBeEmpty();
});

// =========================================================================
// 3. FUNGSIONALITAS TANDAI SUDAH DIBACA (Mark as Read)
// =========================================================================

test('user can mark a single notification as read', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\TestNotification',
        'data' => [
            'message' => 'Notification content',
        ],
        'read_at' => null,
    ]);

    expect($user->unreadNotifications()->count())->toBe(1);

    $response = $this->actingAs($user)->postJson(route('notifications.read', ['id' => $notification->id]));

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    // Pastikan status di database terupdate
    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
    expect($user->unreadNotifications()->count())->toBe(0);
});

test('user cannot mark someone else\'s notification as read', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $notificationB = $userB->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\TestNotification',
        'data' => [
            'message' => 'Notification for B',
        ],
        'read_at' => null,
    ]);

    // Login sebagai User A dan coba tandai notifikasi milik B sebagai read
    $response = $this->actingAs($userA)->postJson(route('notifications.read', ['id' => $notificationB->id]));

    // Harus mengembalikan 404 karena controller mencari di notifications milik User A
    $response->assertStatus(404);

    // Pastikan notifikasi B tetap unread di database
    $notificationB->refresh();
    expect($notificationB->read_at)->toBeNull();
});

// =========================================================================
// 4. FUNGSIONALITAS TANDAI SEMUA DIBACA (Mark All As Read)
// =========================================================================

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();

    // Buat 3 notifikasi unread
    for ($i = 0; $i < 3; $i++) {
        $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'data' => [
                'message' => "Notification {$i}",
            ],
            'read_at' => null,
        ]);
    }

    expect($user->unreadNotifications()->count())->toBe(3);

    $response = $this->actingAs($user)->postJson(route('notifications.read-all'));

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    // Pastikan semua notifikasi sudah terbaca
    expect($user->unreadNotifications()->count())->toBe(0);
    expect($user->notifications()->whereNull('read_at')->count())->toBe(0);
});

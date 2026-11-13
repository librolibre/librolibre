<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** Laravel's notifications table works with our User (database channel). */
    public function test_user_can_receive_and_read_a_database_notification(): void
    {
        $user = User::factory()->create();

        $user->notify(new class extends Notification
        {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return ['text' => 'Ane quiere tu libro.'];
            }
        });

        $this->assertSame(1, $user->unreadNotifications()->count());
        $this->assertSame('Ane quiere tu libro.', $user->notifications->first()->data['text']);

        $user->unreadNotifications->markAsRead();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}

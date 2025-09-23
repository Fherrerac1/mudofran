<?php

namespace App\Events;

use App\Models\Notificaciones;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MyNotifications implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Notificaciones
     */
    public $notification;
    /**
     * Create a new event instance.
     */
    public function __construct(Notificaciones $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        return ['notifications-live'];
    }

    public function broadcastAs()
    {
        return 'notifications-updated';
    }
}

How It Works
1. Backend: Broadcasting Notifications
Model Trigger:
When a new notification is created in the Notificaciones model, it triggers an event via the boot method:

php
Copy
Edit
protected static function boot()
{
    parent::boot();

    static::created(function ($notification) {
        event(new MyNotifications($notification));
    });
}
Event:
The MyNotifications event implements Laravel's ShouldBroadcast interface, which automatically pushes the notification data to Pusher:

php
Copy
Edit
class MyNotifications implements ShouldBroadcast
{
    public $notification;

    public function __construct(Notificaciones $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        return ['notifications-live']; // Pusher channel
    }

    public function broadcastAs()
    {
        return 'notifications-updated'; // Event name
    }
}
Pusher Channel:
The event broadcasts on the notifications-live channel with the event name notifications-updated.

2. Frontend: Listening for Notifications
Pusher Client Initialization:
The Vue component initializes a Pusher client with the app key and cluster from environment variables:

js
Copy
Edit
const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});
Subscribe to Channel:
The client subscribes to the notifications-live channel:

js
Copy
Edit
const channel = pusher.subscribe('notifications-live');
Bind to Event:
The client listens for the notifications-updated event and receives new notification data in real time:

js
Copy
Edit
channel.bind('notifications-updated', ({ notification }) => {
    // Handle incoming notification
});
Notification Filtering:
Before adding the incoming notification to the UI, the Vue app applies role-based and "viewed" filters to ensure users only see relevant, unseen notifications.

3. Configuration (Environment Variables)
Pusher requires configuration in your .env file and frontend environment variables:

env
Copy
Edit
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=your-cluster

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
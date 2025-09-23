<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Factory;

class SendPushNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $fcmTokens;

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param string $message
     * @param array $fcmTokens
     */
    public function __construct($title, $message, $fcmTokens)
    {
        $this->title = $title;
        $this->message = $message;
        $this->fcmTokens = $fcmTokens;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['firebase'];
    }

    /**
     * Send the notification via Firebase Cloud Messaging.
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function toFirebase($notifiable)
    {
        $filePath = storage_path(config('firebase.projects.app.credentials.file'));

        if (!file_exists($filePath)) {
            dd("File does not exist: " . $filePath);
        }

        // Continue with Firebase setup if needed
        $factory = (new Factory)->withServiceAccount($filePath);
        $messaging = $factory->createMessaging();


        $notification = FirebaseNotification::create($this->title, $this->message);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData([
                // Additional data can be added here
            ]);


        ;

        foreach ($this->fcmTokens as $token) {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification) // optional
                ->withData([]);

            try {
                $messaging->send($message);
                // Optionally log success or do something else here
            } catch (\Exception $e) {
                // Handle the exception (e.g., log the error message)
                // You can log the error, or handle it in another way
                error_log("Failed to send message to token {$token}: " . $e->getMessage());
                // Optionally continue or handle specific cases here
            }
        }

    }

}

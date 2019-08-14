<?php

namespace Hypersistence\Notifications;

//use Illuminate\Support\Str;
//use Illuminate\Contracts\Notifications\Dispatcher;
use Hypersistence\Notifications\Notification;
use Hypersistence\Hypersistence;

trait RoutesNotifications {

    /**
     * Send the given notification.
     *
     * @param  mixed  $instance
     * @return void
     */
    public function notify($instance) {

        $notification = new Notification();
        $class = $this;
        $notification->setType(get_class($instance));
        $notification->setData(json_encode($instance->toDatabase($class)));
        $notification->setNotifiableId($class->getId());
        $notification->setNotifiableType($class->getTableName());
        $notification->setCreatedAt(date("Y-m-d H:i:s"));
        if (!$notification->save()) {
            abort(500, "Erro ao valvar notificação.");
        } else {
            Hypersistence::commit();
            return $notification->getId();
        }
    }

    /**
     * Remove the notification.
     *
     * @return void
     */
    public function removeNotification($id) {
        $notification = new Notification();
        $notification->setId($id);
        if (!$notification->delete()) {
            abort(500, "Erro ao remover notificação.");
        }
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead($id) {
        $notification = new Notification();
        $notification->setId($id);
        $notification->load();
//        abort(500, "Erro ao valvar notificação = " . print_r($notification, 1));
        if ($notification->isLoaded() && is_null($notification->getReadAt())) {
            $notification->setReadAt(date("Y-m-d H:i:s"));
            if (!$notification->save()) {
                abort(500, "Erro ao marcar notificação como lida");
            } else {
                Hypersistence::commit();
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread() {
//        if (! is_null($this->read_at)) {
//            $this->forceFill(['read_at' => null])->save();
//        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read() {
//        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread() {
//        return $this->read_at === null;
    }

    /**
     * Send the given notification immediately.
     *
     * @param  mixed  $instance
     * @param  array|null  $channels
     * @return void
     */
//    public function notifyNow($instance, array $channels = null)
//    {
//        app(Dispatcher::class)->sendNow($this, $instance, $channels);
//    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @param  string  $driver
     * @return mixed
     */
//    public function routeNotificationFor($driver) {
//        if (method_exists($this, $method = 'routeNotificationFor' . Str::studly($driver))) {
//            return $this->{$method}();
//        }
//
//        switch ($driver) {
//            case 'database':
//                return $this->notifications();
//            case 'mail':
//                return $this->email;
//            case 'nexmo':
//                return $this->phone_number;
//        }
//    }
}

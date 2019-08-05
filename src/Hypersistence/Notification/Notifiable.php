<?php

namespace Hypersistence\Notifications;

trait Notifiable {
    use HasDatabaseNotifications, RoutesNotifications;
}

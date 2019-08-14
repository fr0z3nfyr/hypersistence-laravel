<?php

namespace Hypersistence\Notifications;

trait HasDatabaseNotifications {

    /**
     * Get the entity's notifications.
     */
    public function notifications() {
        $class = $this;
        $n = new \Hypersistence\Notifications\Notification();
        $n->setNotifiableId($class->getId());
        $n->setNotifiableType($class->getTableName());
        $prepare = $n->search();
        $prepare->orderBy('id', 'desc');
        $list = $prepare->execute();
        return $list;
    }

    /**
     * Get the entity's read notifications.
     */
    public function totalNotifications($read = null) {
        $class = $this;
        $n = new \Hypersistence\Notifications\Notification();
        $n->setNotifiableId($class->getId());
        $n->setNotifiableType($class->getTableName());
        $prepare = $n->search()->setPage(1)->setRows(1);
        if (!is_null($read)) {
            if ($read) {
                $prepare->filter("readAt", "NOT NULL", "IS");
            } else {
                $prepare->filter("readAt", "NULL", "IS");
            }
        }
        $list = $prepare->execute();
        $prepare->getTotalRows();
        return $prepare->getTotalRows();
    }

    /**
     * Get total the entity's read notifications.
     */
    public function totalReadNotifications() {
        return $this->totalNotifications(true);
    }

    /**
     * Get total the entity's unread notifications.
     */
    public function totalUnreadNotifications() {
        return $this->totalNotifications(false);
    }

}

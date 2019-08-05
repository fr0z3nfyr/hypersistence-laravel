<?php

namespace Hypersistence\Notifications;

use Hypersistence\Hypersistence;

/**
 * @table(notifications)
 */
class Notification extends Hypersistence {

    /**
     * @primaryKey
     * @column(id)
     * @fillable
     */
    private $id;

    /**
     * @column(type)
     * @fillable
     */
    private $type;
    
    /**
     * @column(notifiable_id)
     * @fillable
     */
    private $notifiableId;
    
    /**
     * @column(notifiable_type)
     * @fillable
     */
    private $notifiableType;
    
    /**
     * @column(data)
     * @fillable
     */
    private $data;
    
    /**
     * @column(read_at)
     * @fillable
     */
    private $readAt;
    /**
     * @column(created_at)
     * @fillable
     */
    private $createdAt;
    /**
     * @column(updated_at)
     * @fillable
     */
    private $updatedAt;

    public function getId() {
        return $this->id;
    }

    public function getType() {
        return $this->type;
    }

    public function getNotifiableId() {
        return $this->notifiableId;
    }

    public function getNotifiableType() {
        return $this->notifiableType;
    }

    public function getData() {
        return $this->data;
    }

    public function getReadAt() {
        return $this->readAt;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setNotifiableId($notifiableId) {
        $this->notifiableId = $notifiableId;
    }

    public function setNotifiableType($notifiableType) {
        $this->notifiableType = $notifiableType;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function setReadAt($readAt) {
        $this->readAt = $readAt;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
    
}

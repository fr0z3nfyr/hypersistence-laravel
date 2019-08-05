<?php

namespace Hypersistence\Console;

use Hypersistence\Hypersistence;
use Illuminate\Console\Command;

class CreateNotificationTable extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hypersistence:make-notifications-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create table for notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->exec();
    }

    private function exec() {
        $stmt = Hypersistence::getDBConnection()->prepare("SHOW TABLES LIKE 'notifications'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $this->info("Table already exists");
            exit;
        }
        $sql = "CREATE TABLE notifications("
                . "id INT(11) UNSIGNED AUTO_INCREMENT,"
                . "type VARCHAR(255) NOT NULL,"
                . "notifiable_id INT(11) NOT NULL,"
                . "notifiable_type VARCHAR(255) NOT NULL,"
                . "data TEXT NOT NULL,"
                . "read_at DATETIME,"
                . "created_at DATETIME,"
                . "updated_at DATETIME,"
                . "PRIMARY KEY (id),"
                . "INDEX idx_notifications_id (id),"
                . "INDEX idx_notifications_read_at_table (read_at),"
                . "INDEX idx_notifications_notifiable_id_table (notifiable_id),"
                . "INDEX idx_notifications_notifiable_type_class (notifiable_type))";

        $stmt = Hypersistence::getDBConnection()->prepare($sql);
        if ($stmt->execute()) {
            Hypersistence::commit();
            $this->info("Table created successfully!");
            exit;
        } else {
            Hypersistence::rollback();
            $this->error("Error to create table!");
            exit;
        }


        Hypersistence::rollback();
    }

}

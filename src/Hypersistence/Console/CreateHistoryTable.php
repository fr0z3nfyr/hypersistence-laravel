<?php

namespace Hypersistence\Console;

use Hypersistence\Hypersistence;
use Illuminate\Console\Command;

class CreateHistoryTable extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hypersistence:make-history-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create table to store records change history';

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
        $stmt = Hypersistence::getDBConnection()->prepare("SHOW TABLES LIKE 'history'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $this->info("Table already exists");
            exit;
        }

        $userClass = config('auth.providers.users.model');
        $user = new $userClass();
        $tableName = $user->getTableName();
        $primaryKeyField = $user->getPrimaryKeyField();
        $sqlType = "show Columns from $tableName where Field = '$primaryKeyField'";

        $stmt = Hypersistence::getDBConnection()->prepare($sqlType);
        $stmt->execute();
        $r = $stmt->fetchObject();
        $type = strtoupper($r->Type);

        $sql = "CREATE TABLE history("
                . "id INT(11) UNSIGNED AUTO_INCREMENT,"
                . "reference_table VARCHAR(50) NOT NULL,"
                . "reference_id INT(11) NOT NULL,"
                . "description VARCHAR(255) NOT NULL,"
                . "`date` DATETIME NOT NULL,"
                . "author_id $type NULL,"
                . "PRIMARY KEY (id),"
                . "FOREIGN KEY (author_id) REFERENCES $tableName ($primaryKeyField) ON DELETE NO ACTION,"
                . "INDEX idx_history_id (id),"
                . "INDEX idx_history_reference_table (reference_table),"
                . "INDEX idx_history_reference_id (reference_id),"
                . "INDEX idx_history_author_id (author_id))";

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

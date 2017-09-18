<?php

namespace Hypersistence\Core;

class DB extends \PDO {

    /**
     * @var DB
     */
    private static $conn = null;

    public function __construct($dsn, $username, $passwd, $options) {
        parent::__construct($dsn, $username, $passwd, $options);
    }

    /**
     * 
     * @return \Hypersistence\Core\DB
     */
    public static function &getDBConnection() {
        if (!is_null(self::$conn) && self::$conn instanceof DB) {
            return self::$conn;
        } else {
            self::$conn = new DB(env("DB_CONNECTION", "mysql") . ":"
                    . "host=" . env("DB_HOST", "localhost") . ";"
                    . "dbname=" . env("DB_DATABASE", "laravel_db") . ";"
                    . "charset=" . env("DB_CHARSET", "utf8mb4") . ","
                    . env("DB_USERNAME", "root") . ", "
                    . env("DB_PASSWORD", "root"), array(
                self::ATTR_PERSISTENT => true,
                self::ATTR_STATEMENT_CLASS => array('\Hypersistence\Core\Statement'),
                self::ATTR_PERSISTENT => false)
            );

            if (!self::$conn->inTransaction())
                self::$conn->beginTransaction();
            return self::$conn;
        }
    }

    public static function destroy() {
        self::$conn = null;
    }

    public function commit() {
        $r = parent::commit();
        parent::beginTransaction();
        return $r;
    }

    public function rollback() {
        $r = parent::rollBack();
        parent::beginTransaction();
        return $r;
    }

}

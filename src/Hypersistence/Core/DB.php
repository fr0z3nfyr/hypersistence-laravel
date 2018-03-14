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
            $driver = config("database.default");
            $host = config("database.connections.$driver.host");
            $database = config("database.connections.$driver.database");
            $username = config("database.connections.$driver.username");
            $password = config("database.connections.$driver.password");
            $charset = config("database.connections.$driver.charset");
            self::$conn = new DB($driver . ":"
                    . "host=" . $host . ";"
                    . "dbname=" . $database . ";"
                    . "charset=" . $charset, $username, $password, array(
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

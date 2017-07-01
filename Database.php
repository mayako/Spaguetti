<?php

namespace Spaguetti;

use Spaguetti\Database\Connection;
use Spaguetti\Database\Query;
use \PDO;
use \Exception;
use \PDOStatement;
use \Closure;


class Database
{
    /**
     * Overwrite access to connect
     * @param  string $host
     * @param  string $dbname
     * @param  string $username
     * @param  string $password
     */
    public function connect_to($host, $database, $username, $password = '')
    {
        return Connection::to(array
            (
                'host'     => $host,
                'database' => $database,
                'username' => $username,
                'password' => $password
            )
        );
    }

    public function statement($query, array $binds = array())
    {
        return self::get_connection()->get_pdo()->prepare($query)->execute($binds);
    }

    /**
     * Run a select statement.
     * @param  string $query
     * @param  array  $binds
     * @return array
     */
    public function select($query, array $binds = array(), array $options = array())
    {
        return self::select_run($query, $binds, $options)->fetchAll();
    }

    /**
     * Alias of select method
     * @param  string $query
     * @param  array  $binds
     * @return array
     */
    public function select_all($query, array $binds = array(), array $options = array())
    {
        return self::select($query, $binds, $options);
    }

    /**
     * Run a select statement and return only one record.
     * @param  string $query
     * @param  array  $binds
     * @return mixed
     */
    public function select_one($query, array $binds = array(), array $options = array())
    {
        return self::select_run($query, $binds, $options)->fetch() ?: null;
    }

    /**
     * Execute a select statement
     * @param  string $query
     * @param  array  $binds
     * @param  array  $options
     * @return PDOStatement
     */
    protected function select_run($query, array $binds = array(), array $options = array())
    {
        $stmt = self::get_connection()->get_pdo()->prepare($query);

        self::set_fetch_mode($stmt, $options);

        $stmt->execute($binds);

        return $stmt;
    }

    /**
     * Set the fetch mode
     * @param PDOStatement $stmt
     * @param array        $options
     */
    protected function set_fetch_mode(PDOStatement $stmt, array $options)
    {
        if (!($mode = array_get($options, 'as'))) {
            return;
        }

        $modes = array(
            'assoc' => PDO::FETCH_ASSOC,
            'object' => PDO::FETCH_OBJ
        );

        if (!array_key_exists($mode, $modes)) {
            throw new Exception("Fetch mode invalid, only accept 'assoc' or 'object'", 1);
        }

        $stmt->setFetchMode($modes[$mode]);
    }

    /**
     * Run an insert statement and return the id inserted.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public function insert($query, array $binds = array())
    {
        return self::statement($query, $binds);
    }

    /**
     * Run an insert statement and return the id inserted.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public function insert_get_id($query, array $binds = array())
    {
        self::insert($query, $binds);

        $id = self::get_connection()->get_pdo()->lastInsertId();

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Run an update statement.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public function update($query, array $binds = array())
    {
        return self::affect_rows($query, $binds);
    }

    /**
     * Run a delete statement.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public function delete($query, array $binds = array())
    {
        return self::affect_rows($query, $binds);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    protected function affect_rows($query, array $binds = array())
    {
        $stmt = self::get_connection()->get_pdo()->prepare($query);

        $stmt->execute($binds);

        return $stmt->rowCount();
    }

    /**
     * Run a SQL statement in a transaction.
     * @param  Closure $callback
     * @return mixed
     */
    public function transaction(Closure $callback)
    {
        $pdo = self::get_connection()->get_pdo();

        $pdo->beginTransaction();

        try {
            $rs = $callback();

            $pdo->commit();

            return $rs;
        }

        catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Initiates a transaction
     * @return boolean
     */
    public function begin_transaction()
    {
        return self::get_connection()->get_pdo()->beginTransaction();
    }

    /**
     * Commits a transaction
     * @return boolean
     */
    public function commit()
    {
        return self::get_connection()->get_pdo()->commit();
    }

    /**
     * Rolls back a transaction
     * @return boolean
     */
    public function roll_back()
    {
        $pdo = self::get_connection()->get_pdo();

        if ($pdo->inTransaction()) {
            return $pdo->rollBack();
        }

        return true;
    }

    /**
     * Create a new query instance.
     * @param  mixed $table
     * @return Query
     */
    public function table($table)
    {
        $query = new Query();

        return $query->from($table);
    }

    /**
     * Create a new query instance.
     * @param  mixed $table
     * @return Query
     */
    public function from($table)
    {
        return self::table($table);
    }

    /**
     * Get a Connection instance
     * @return Database/Connection
     */
    public function get_connection()
    {
        return Connection::get_instance();
    }

    /**
     * It allows the static call.
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(__CLASS__, $method), $args);
    }
}
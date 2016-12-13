<?php

class Database
{
    /**
    * Database connection instance.
    * @var Connection
    */
    private $connection;

    /**
    * SQL builder instance.
    * @var SQLBuilder
    */
    private $builder;

    /**
     * SQL string to execute.
     * @var string
     */
    private $sql;

    /**
    * Params to bind within query
    * @var array
    */
    private $binds = array(
        'select' => array(),
        'update' => array(),
        'join'   => array(),
        'where'  => array(),
        'having' => array()
    );

    /**
     * Statement type.
     * @var string
     */
    public $type = 'select';

    /**
     * Fetch mode
     * @var array
     */
    private $fetch_mode;

    /**
     * Fetch mode Class
     * @var string
     */
    private $fetch_class;

    /**
     * Create a new query builder instance.
     * @param string $table
     */
    public function __construct()
    {
        $this->connection = Database\Connection::get_instance();
        // $this->builder = new SQLBuilder();
    }

    /**
     * Overwrite access to connect
     * @param  string $host
     * @param  string $dbname
     * @param  string $username
     * @param  string $password
     */
    public static function connect_to($host, $database, $username, $password = '')
    {
        return Database\Connection::to(array
            (
                'host'     => $host,
                'database' => $database,
                'username' => $username,
                'password' => $password
            )
        );
    }

    /**
     * Run a select statement.
     * @param  string $query
     * @param  array  $bindings
     * @return array
     */
    public static function select_sql($query, array $binds = array())
    {
        $that = new self();

        $that->sql = $query;

        $that->binds = $binds;

        return $that;
    }

    /**
     * Run an insert statement and return the id inserted.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public static function insert_sql($query, array $binds = array())
    {
        $that = new self();

        $that->connection->get_pdo()->prepare($query)->execute($binds);

        $id = $that->connection->get_pdo()->lastInsertId();

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Run an update statement.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public static function update_sql($query, array $binds = array())
    {
        $that = new self();

        return $that->affect_rows($query, $binds);
    }

    /**
     * Run a delete statement.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    public static function delete_sql($query, array $binds = array())
    {
        $that = new self();

        return $that->affect_rows($query, $binds);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     * @param  string $query
     * @param  array  $binds
     * @return int
     */
    protected function affect_rows($query, array $binds = array())
    {
        $stmt = $this->connection->get_pdo()->prepare($query);

        $stmt->execute($binds);

        return $stmt->rowCount();
    }

    /**
     * Return an array with all rows
     * @return array
     */
    public function all()
    {
        return $this->run_select()->fetchAll() ?: null;
    }

    /**
     * Return a row
     * @return mixed
     */
    public function one()
    {
        return $this->run_select()->fetch() ?: null;
    }

    /**
     * Fetch rows
     * @param  callable $callback
     * @return mixed
     */
    public function fetch($callback)
    {
        $rows = $this->all();

        $result = array();

        foreach ($rows as $row) {
            $result[] = $callback($row);
        }

        return count($result) > 1 ? $result : reset($result);
    }

    /**
     * Set fetch as array assiciative
     * @return Database
     */
    public function as_assoc()
    {
        $this->fetch_mode = PDO::FETCH_ASSOC;
        return $this;
    }

    /**
     * Set fecth as object
     * @param  string $classname
     * @return Database
     */
    public function as_object($classname = null)
    {
        $this->fetch_mode = $classname ? PDO::FETCH_CLASS : PDO::FETCH_OBJ;

        if ($classname) {
            $this->fetch_class = $classname;
        }

        return $this;
    }

    /**
     * Fet fetch mode
     * @return int
     */
    public function get_fetch_mode()
    {
        return $this->fetch_mode ?: $this->connection->get_fetch_mode();
    }

    /**
     * Get fetch class
     * @return string
     */
    public function get_fetch_class() {
        return $this->fetch_class;
    }

    /**
     * Get SQL statement into string
     * @return string
     */
    public function to_sql()
    {
        if ($this->sql == null) {
            $this->sql = 'Contruccion de SQL (comming soon)';
        }

        return $this->sql;
    }

    /**
     * Run a select query
     * @return PDOStatement
     */
    protected function run_select()
    {
        $stmt = $this->connection->get_pdo()->prepare($this->to_sql());

        if ($class = $this->get_fetch_class()) {
            $stmt->setFetchMode($this->get_fetch_mode() | PDO::FETCH_PROPS_LATE, $class);
        } else {
            $stmt->setFetchMode($this->get_fetch_mode());
        }

        $stmt->execute($this->binds);

        return $stmt;
    }


    /**
     * Allows treat the object as a string
     * @return string
     */
    public function __toString() {
        return $this->to_sql();
    }
}
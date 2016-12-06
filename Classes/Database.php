<?php

class Database
{
    const QUERY_SELECT = 1;
    const QUERY_INSERT = 2;

    /**
     * SQL string to execute.
     * @var string
     */
    private $sql;

    /**
    * Params to bind within query
    * @var array
    */
    private $bindings = array(
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
    public $type = Database::QUERY_SELECT;

    /**
     * Fetch mode
     * @var int
     */
    public $fetch_mode;

    /**
     * Create a new query builder instance.
     * @param string $table
     */
    public function __construct()
    {
        $this->fetch_mode = PDO::FETCH_OBJ;
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

    public static function query($query, array $binds = array())
    {
        $that = new self();
        $that->sql = $query;
        $that->binds = $binds;

        return $that;
    }

    public function as_assoc()
    {
        $this->fetch_mode = PDO::FETCH_ASSOC;
        return $this;
    }

    public function all()
    {
        $stmt = Database\Connection::get_instance()->get_pdo()->prepare($this->sql);
        $stmt->setFetchMode($this->fetch_mode);
        $stmt->execute($this->binds);

        return $stmt->fetchAll();
    }
}
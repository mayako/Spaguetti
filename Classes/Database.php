<?php

class Database extends Statement
{
    /**
    * Database connection instance.
    * @var Connection
    */
    private $db;

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

    public static function insert($query, array $binds = array())
    {
        $that = new self();

        $that->sql = $query;

        $that->binds = $binds;


    }
}
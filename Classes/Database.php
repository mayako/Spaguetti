<?php

class Database
{
    const QUERY_SELECT = 1;
    const QUERY_INSERT = 2;

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
    public $type = Database::QUERY_SELECT;

    /**
     * Fetch mode
     * @var array
     */
    public $fetch_mode = array();

    private $fetch_mode_options = array(
        'assoc' => PDO::FETCH_ASSOC,
        'both'  => PDO::FETCH_BOTH,
        'bound' => PDO::FETCH_BOUND,
        'class' => PDO::FETCH_CLASS,
        'into'  => PDO::FETCH_INTO,
        'lazy'  => PDO::FETCH_LAZY,
        'named' => PDO::FETCH_NAMED,
        'num'   => PDO::FETCH_NUM,
        'obj'   => PDO::FETCH_OBJ
    );

    /**
     * Create a new query builder instance.
     * @param string $table
     */
    public function __construct()
    {
        $this->fetch_as(Database\Connection::get_instance()->get_fetch_mode());
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

    public function all()
    {
        $stmt = Database\Connection::get_instance()->get_pdo()->prepare($this->to_sql());

        $stmt->setFetchMode($this->get_fetch_mode());

        $stmt->execute($this->binds);

        return $stmt->fetchAll();
    }

    public function one()
    {
        $stmt = Database\Connection::get_instance()->get_pdo()->prepare($this->to_sql());

        $stmt->setFetchMode($this->get_fetch_mode());

        $stmt->execute($this->binds);

        return $stmt->fetch();
    }

    public function fetch($callback)
    {
        $rows = $this->all();

        return array_map($callback, $rows);
    }


    # FETCHING MODE
    public function as_assoc()
    {
        $this->fetch_as('assoc');
        return $this;
    }

    public function as_object($classname = null)
    {


        $this->fetch_as('obj');
    }

    public function get_fetch_mode()
    {
        return $this->fetch_mode;
    }

    public function fecth_as($mode, $colno_or_classname_or_object = null, array $ctorargs = array())
    {
        if (is_string($mode)) {
            if (!$mode = array_get($this->fetch_mode_options, $mode)) {
                throw new Exception("Fetch mode option doesnot valid");
            }
        }

        $this->fetch_mode = $mode;
    }

    public function to_sql()
    {
        if ($this->sql == null) {
            $this->sql = 'Contruccion de SQL (comming soon)';
        }

        return $this->sql;
    }
}
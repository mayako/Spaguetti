<?php

final class Connection
{
    /**
    * Singleton instance.
    * @var Connection
    */
    private static $instance;

    /**
    * PDO instance.
    * @var PDO
    */
    private $pdo;

    /**
     * Connection info
     * @var array
     */
    private static $dbaccess = array(
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'test',
        'charset'  => 'utf8',
        'username' => 'root',
        'password' => '',
        'options'  => array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        )
    );

    /**
    * Create a new database connection instance.
    */
    private function __construct()
    {
        echo "Nueva Intancia";
        echo '<br>';

        $this->set_charset();

        $this->set_pdo();
    }


    private function set_pdo()
    {
        try {
            $this->pdo = new PDO($this->get_dsn(), $this->get_username(), $this->get_password(), $this->get_options());
        } catch (Exception $e) {
            throw new Exception('Oops! Falló la conexión con la base de datos: '.$e->getMessage());
        }
    }

    /**
    * Get the database connection instance.
    * @return Connnection
    */
    public static function get()
    {
        return self::$instance ?: self::$instance = new self();
    }

    /**
    * Get the current PDO connection.
    * @return PDO
    */
    public function get_pdo()
    {
        return $this->pdo;
    }

    public function get_dsn()
    {
        $dsn = array_except(self::$dbaccess, array('username', 'password', 'options'));

        array_rename_keys($dsn, array('database' => 'dbname'));

        return 'mysql:'.http_build_query(array_filter($dsn), '', ';');
    }

    public function get_database()
    {
        return self::$dbaccess['database'];
    }

    public function get_username()
    {
        return self::$dbaccess['username'];
    }

    private function get_password()
    {
        return self::$dbaccess['password'];
    }

    private function get_options()
    {
        return self::$dbaccess['options'];
    }

    /**
     * Set the option MYSQL_ATTR_INIT_COMMAND to old PHP versions
     */
    private function set_charset()
    {
        $charset = @self::$dbaccess['charset'];

        if ($charset && version_compare(PHP_VERSION, '5.3.6', '<='))
        {
            self::$dbaccess['options'][PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '$charset'";
        }
    }

    public static function to()
    {

    }

    /**
     * Denies cloning
     */
    private function __clone() { }
}
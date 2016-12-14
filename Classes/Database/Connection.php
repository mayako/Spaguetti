<?php

namespace Database;

use \PDO as PDO;
use \Exception as Exception;

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
    private static $config = array(
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'test',
        'charset'  => 'utf8',
        'username' => 'root',
        'password' => '',
        'options'  => array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );

    /**
    * Create a new database connection instance.
    */
    protected function __construct()
    {
        $this->set_charset();

        $this->set_pdo();
    }


    protected function set_pdo()
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
    public static function get_instance()
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

    /**
     * Build DSN string
     * @return string
     */
    public function get_dsn()
    {
        if ($dsn = array_take(self::$config, 'dsn')) {
            return $dsn;
        }

        $dsn = array_except(self::$config, array('username', 'password', 'options'));

        array_rename_keys($dsn, array('database' => 'dbname'));

        return 'mysql:'.http_build_query(array_filter($dsn), '', ';');
    }

    /**
     * Get Database name
     * @return string
     */
    public function get_database()
    {
        return self::$config['database'];
    }

    /**
     * Get User name
     * @return string
     */
    private function get_username()
    {
        return self::$config['username'];
    }

    /**
     * Get password
     * @return string
     */
    private function get_password()
    {
        return self::$config['password'];
    }

    /**
     * Get options array
     * @return array
     */
    private function get_options()
    {
        return self::$config['options'];
    }

    /**
     * Get fetch mode
     * @return int
     */
    public function get_fetch_mode()
    {
        return @self::$config['options'][PDO::ATTR_DEFAULT_FETCH_MODE];
    }

    /**
     * Set the option MYSQL_ATTR_INIT_COMMAND to old PHP versions
     */
    private function set_charset()
    {
        $charset = @self::$config['charset'];

        if ($charset && version_compare(PHP_VERSION, '5.3.6', '<=')) {
            self::$config['options'][PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '$charset'";
        }
    }

    /**
     * Overwrite access data to connect
     * @param  array  $config
     */
    public static function to(array $config)
    {
        if ($file = array_take($config, 'file')) {
            $config = require $file;
        }

        self::$config = array_replace_recursive(self::$config, $config);
    }

    /**
     * Denies cloning
     */
    private function __clone() { }
}
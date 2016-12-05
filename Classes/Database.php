<?php

class Database
{
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


        return $this;
    }

    public function as_assoc()
    {
        $this->fetch_mode = PDO::FETCH_ASSOC;
    }

    public function all()
    {
        if ($this->type == Database::TYPE_STATEMENT) {
            $stmt = Database\Connection::get_instance()->get_pdo()->prepare($query);
            $stmt->setFetchMode($this->fetch_mode());
            $stmt->execute($binds);
        }

        return $stmt->fetchAll();
    }
}
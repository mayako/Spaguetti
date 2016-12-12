<?php

namespace Database;

class Statement
{
    public function __construct()
    {
        $this->connection = Database\Connection::get_instance();
    }


    public function all()
    {
        return $this->run_select()->fetchAll() ?: null;
    }

    public function one()
    {
        return $this->run_select()->fetch() ?: null;
    }

    public function fetch($callback)
    {
        $rows = $this->all();

        return array_map($callback, $rows);
    }


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


    # FETCHING MODE
    public function as_assoc()
    {
        $this->fetch_mode = PDO::FETCH_ASSOC;
        return $this;
    }

    public function as_object($classname = null)
    {
        $this->fetch_mode = $classname ? PDO::FETCH_CLASS : PDO::FETCH_OBJ;

        if ($classname) {
            $this->fetch_class = $classname;
        }

        return $this;
    }

    public function get_fetch_mode()
    {
        return $this->fetch_mode ?: $this->connection->get_fetch_mode();
    }

    public function get_fetch_class() {
        return $this->fetch_class;
    }

    public function fetch_as($mode)
    {
        if (is_string($mode)) {
            if ()
        }
    }

    public function to_sql()
    {
        if ($this->sql == null) {
            $this->sql = 'Contruccion de SQL (comming soon)';
        }

        return $this->sql;
    }
}
<?php

/**
 * Overwrite access to connect
 * @param  string $host
 * @param  string $dbname
 * @param  string $username
 * @param  string $password
 */
function db_connect($host, $database, $username, $password = '')
{
    return Database::connect_to($host, $database, $username, $password);
}

/**
 * Run a select statement.
 * @param  string $query
 * @param  array  $binds
 * @return array
 */
function db_select($query, array $binds = array(), array $options = array())
{
    return Database::select($query, $binds, $options);
}

/**
 * Alias of db_select function
 * @param  string $query
 * @param  array  $binds
 * @return array
 */
function db_select_all($query, array $binds = array(), array $options = array())
{
    return Database::select_all($query, $binds, $options);
}

/**
 * Run a select statement and return only one record.
 * @param  string $query
 * @param  array  $binds
 * @return mixed
 */
function db_select_one($query, array $binds = array(), array $options = array())
{
    return Database::select_one($query, $binds, $options);
}

/**
 * Run an insert statement.
 * @param  string $query
 * @param  array  $binds
 * @return int
 */
function db_insert($query, array $binds = array())
{
    return Database::insert($query, $binds);
}

/**
 * Run an insert statement and return the id inserted.
 * @param  string $query
 * @param  array  $binds
 * @return int
 */
function db_insert_get_id($query, array $binds = array())
{
    return Database::insert_get_id($query, $binds);
}

/**
 * Run an update statement.
 * @param  string $query
 * @param  array  $binds
 * @return int
 */
function db_update($query, array $binds = array())
{
    return Database::update($query, $binds);
}

/**
 * Run a delete statement.
 * @param  string $query
 * @param  array  $binds
 * @return int
 */
function db_delete($query, array $binds = array())
{
    return Database::delete($query, $binds);
}

/**
 * Run a SQL statement in a transaction.
 * @param  Closure $callback
 * @return mixed
 */
function db_transaction(Closure $callback)
{
    return Database::transaction($callback);
}

/**
 * Initiates a transaction
 * @return boolean
 */
function db_begin_transaction()
{
    return Database::begin_transaction();
}

/**
 * Commits a transaction
 * @return boolean
 */
function db_commit()
{
    return Database::commit();
}

/**
 * Rolls back a transaction
 * @return boolean
 */
function db_roll_back()
{
    return Database::roll_back();
}

/**
 * Create a new query instance.
 * @param  mixed $table
 * @return Query
 */
function db_table($table) {
    return Database::table($table);
}

/**
 * Create a new query instance.
 * @param  mixed $table
 * @return Query
 */
function db_from($table)
{
    return Database::table($table);
}


<?php

namespace Database;

class Query
{
    /**
    * Database instance.
    * @var Connection
    */
    private $db;

    /**
    * SQL builder instance.
    * @var SQLBuilder
    */
    private $builder;

    /**
     * Table instances
     * @var array
     */
    private static $tables = array();

    /**
    * Params to bind within query
    * @var array
    */
    public $binds = array(
        'select' => array(),
        'update' => array(),
        'join'   => array(),
        'where'  => array(),
        'having' => array()
    );

    /**
     * SQL string to execute.
     * @var string
     */
    private $sql;

    /**
     * Statement type.
     * @var string
     */
    public $type = 'select';

    /**
     * Fetch mode
     * @var string
     */
    private $fetch_mode;

    /**
    * Columns to return
    * @var array
    */
    public $columns;

    /**
    * Table on which the query will be executed
    * @var string
    */
    public $from;

    /**
     * Joins for the query.
     * @var array
     */
    public $join;

    /**
     * Wheres for the query.
     * @var array
     */
    public $where;

    /**
     * Groups for the query.
     * @var array
     */
    public $group;

    /**
     * Havings for the query.
     * @var [type]
     */
    public $having;

    /**
     * Orders for the query.
     * @var array
     */
    public $order;

    /**
     * Limit for the query
     * @var array
     */
    public $limit;

    /**
     * Offset for the query
     * @var array
     */
    public $offset;

    /**
     * Row to insert or update
     * @var array
     */
    public $rows;

    /**
     * Create a new query builder instance.
     * @param string $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->from($table);
        }

        $this->builder = new SQLBuilder();
        $this->db      = new \Database();
    }

    /**
     * Set the columns to be selected
     * @param  mixed $columns
     * @return Query
     */
    public function select($columns = array('*'))
    {
        $this->columns = array_values_recursive(func_get_args());
        return $this;
    }

    /**
     * Set table on which the query will be executed
     * @param  string $table
     * @return Query
     */
    public function from($table)
    {
        $this->from = $table;
        return $this;
    }

    /**
     * Set a "join" to the query.
     * @param  string $table
     * @param  mixed $column_one
     * @param  string $column_two
     * @param  string $type
     * @return Query
     */
    public function join($table, $column_one = null, $column_two = null, $type = '')
    {
        if (func_num_args() < 3) {
            @list($query, $params) = func_get_args();

            $params = (array) $params;

            $this->join[] = compact('query', 'params');

            $this->add_bind('join', $params);
        }

        else {
            $this->join[] = compact('table', 'column_one', 'column_two', 'type');
        }

        return $this;
    }

    /**
     * Add a "where" to the query.
     * @param  mixed  $query
     * @param  mixed   $params
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function where($query, $params = array(), $boolean = 'and', $not = false)
    {
        if (is_array($query)) {
            return $this->where_assoc($query, $boolean, $not);
        }

        if ($params instanceof Query) {
            return $this->where_sub($query, $params, $boolean, $not);
        }

        $type = 'bind';

        $this->where[] = compact('query', 'params', 'boolean', 'not', 'type');

        $this->add_bind('where', $params);

        return $this;
    }

    /**
     * Add a "where" to the query with syntax column => value.
     * @param  array   $array
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function where_assoc(array $array, $boolean = 'and', $not = false)
    {
        $type = 'assoc';
        foreach ($array as $column => $values) {

            if($values instanceof Query) {
                $this->where_sub($column, $values, $boolean, $not);
                continue;
            }

            if ($values !== null) {
                $this->add_bind('where', $values);
            }

            $this->where[] = compact('column', 'values', 'boolean', 'not', 'type');
        }

        return $this;
    }

    /**
     * Add a sub-query to the query.
     * @param  string  $column
     * @param  mixed   $obj
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function where_sub($column, $query, $boolean = 'and', $not = false)
    {
        $type = 'sub';

        $this->add_bind('where', $query->get_binds_values());

        $this->where[] = compact('column', 'query', 'boolean', 'not', 'type');

        return $this;
    }


    /**
     * Determine if any row exists as a result of this query
     * @return bool
     */
    public function exists()
    {
        $sql = $this->builder->exists($this);

        $rs = $this->db->select_one($sql, $this->get_binds_values());

        $row = (array) $rs;

        return (bool) $row['exist'];
    }

    /**
     * Add a "group by" to the query.
     * @param  mixed $columns
     * @return Query
     */
    public function group($columns)
    {
        $columns = array_values_recursive(func_get_args());

        $this->group = array_merge((array) $this->group, $columns);

        return $this;
    }

    /**
     * Add a "having" to the query.
     * @param  string $query
     * @param  array  $params
     * @return Query
     */
    public function having($query, array $params = array())
    {
        $this->add_bind('having', $params);

        $this->having[] = compact('query', 'params');

        return $this;
    }

    /**
     * Add an "order by" to the query.
     * @param  mixed $columns
     * @return Query
     */
    public function order($column, $order = null)
    {
        $columns = $order == null ? $column : func_get_args();

        $this->order[] = $columns;

        return $this;
    }

    /**
     * Add a "limit" to the query.
     * @param  int $limit
     * @return Query
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add an "offset" to the query.
     * @param  int $offset
     * @return Query
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Insert a new record into the database.
     * @param  array  $rows
     * @param  Query $query
     * @return Query
     */
    public function insert(array $rows, Query $query = null)
    {
        $this->type = 'insert';

        if ($query instanceof Query) {
            return $this->insert_select($rows, $query);
        }

        $this->rows = is_multiple($rows) ? $rows : array($rows);

        $this->columns = array_keys(reset($this->rows));

        $this->set_binds(array_values_recursive_with_keys($rows));

        return $this;
    }

    /**
     * Copy all records returned by query
     * @param  array  $columns
     * @param  Query  $query
     * @return Query
     */
    public function insert_select(array $columns, Query $query)
    {
        $this->columns = $columns;

        $this->set_binds($query->binds);

        $this->rows = $query;

        return $this;
    }

    /**
     * Insert a new record into the database and return its id
     * @param  array  $row
     * @return Query
     */
    public function insert_get_id(array $row)
    {
        if (is_multiple($row)) {
            throw new Exception("Inserting multiple records not accepted");
        }

        $this->type = 'insert_get_id';

        $this->rows = $row;

        $this->columns = array_keys($row);

        $this->set_binds(array_values($row));

        return $this;
    }

    /**
     * Update a record in the database.
     * @param  array  $values
     * @return Query
     */
    public function update(array $values)
    {
        $this->type = 'update';

        $this->rows = $values;

        $this->set_binds(array_values($values), 'update');

        return $this;
    }

    /**
     * Delete any record matching the conditions.
     * @param  array  $wheres
     * @return mixed
     */
    public function delete_where($query, $params = array())
    {
        $this->type = 'delete';
        return $this->where($query, $params);
    }

    /**
     * Delete all record that match with id.
     * @return Query
     */
    public function delete($id, $primary_key = 'id')
    {
        $this->type = 'delete';

        return $this->delete_where(array($primary_key => $id));
    }

    /**
     * Execute the query
     * @return mixed
     */
    public function execute(array $options = array())
    {
        $args = array($this->to_sql(), $this->get_binds_values());

        if ($this->fetch_mode) {
            $options['as'] = $this->fetch_mode;
        }

        if ($this->is_select()) {
            $args[] = $options;
        }

        $result = call_user_func_array(array($this->db, $this->type), $args);

        $this->clean_binds();
        return $result;
    }

    ######################################################
    ########### ALIASES ##################################
    ######################################################

    /**
     * Alias of "select"
     * @param  array  $columns
     * @return Query
     */
    public function columns($columns = array('*'))
    {
        return call_user_func_array(array($this,'select'), func_get_args());
    }

    /**
     * Select only the columns from table.
     * @param  mixed $columns
     * @return Query
     */
    public function select_only($columns)
    {
        return call_user_func_array(array($this,'select'), func_get_args());
    }

    /**
     * Select all column from table except those.
     * @param  mixed $columns
     * @return Query
     */
    public function select_except($columns)
    {
        $exceptions = array_values_recursive(func_get_args());

        $all_columns = $this->get_column_names();

        $columns = array_except($all_columns, $exceptions);

        return $this->select($columns);
    }

    /**
     * Alias of "from"
     * @param  string $table
     * @return Query
     */
    public function table($table)
    {
        $this->from = $table;
        return $this;
    }

    /**
     * Alias of "where"
     * @param  mixed $query
     * @param  mixed  $params
     * @return Query
     */
    public function and_where($query, $params = array(), $not = false)
    {
        return $this->where($query, $params, 'and', $not);
    }

    /**
     * Add a "where" within boolean operator "or" to the query.
     * @param  mixed $query
     * @param  mixed  $params
     * @return Query
     */
    public function or_where($query, $params = array(), $not = false)
    {
        return $this->where($query, $params, 'or', $not);
    }

    /**
     * Add a "where" denying the condition to the query.
     * @param  mixed $query
     * @param  mixed  $params
     * @return Query
     */
    public function where_not($query, $params = array(), $boolean = 'and')
    {
        return $this->where($query, $params, $boolean, true);
    }

    /**
     * Alias  of "where_not"
     * @param  mixed $query
     * @param  mixed  $params
     * @return Query
     */
    public function and_where_not($query, $params = array())
    {
        return $this->where_not($query, $params, 'and');
    }

    /**
     * Add a "where" within boolean operator "or" and denying the condition to the query.
     * @param  mixed $query
     * @param  mixed  $params
     * @return Query
     */
    public function or_where_not($query, $params = array())
    {
        return $this->where_not( $query, $params, 'or');
    }

    /**
     * Add a "like" condition.
     * @param  string $column
     * @param  string $value
     * @return Query
     */
    public function like($column, $value, $boolean = 'and', $not = false)
    {
        $operator = $not ? 'not like' : 'like';
        return $this->where(implode(' ', array($column, $operator, '?')), array($value), $boolean);
    }

    /**
     * Add an "in" condition.
     * @param  string $column
     * @param  mixed $values
     * @return Query
     */
    public function in($column, $values, $boolean = 'and', $not = false)
    {
        if (!is_array($values) and !($values instanceof Query)) {
            $values = array_slice(func_get_args(), 1);
        }

        return $this->where_assoc(array( $column => $values ), $boolean, $not);
    }

    /**
     * Add a "between" condition.
     * @param  string $column
     * @param  mixed $start
     * @param  mixed $end
     * @return Query
     */
    public function between($column, $start, $end, $boolean = 'and', $not = false)
    {
        $operator = $not ? 'not between' : 'between';
        return $this->where($column.' '.$operator.' ? and ? ', array($start, $end), $boolean);
    }

    /**
     * Add an "is null" condition.
     * @param  string  $column
     * @return Query
     */
    public function is_null($column, $boolean = 'and', $not = false)
    {
        return $this->where_assoc(array($column => null), $boolean, $not);
    }

    /**
     * Add an "is null" condition.
     * @param  string  $column
     * @return Query
     */
    public function is_not_null($column, $boolean = 'and')
    {
        return $this->where_assoc(array($column => null), $boolean, true);
    }

    /**
     * Add a "where date" to the query.
     * @param  string  $column
     * @param  string  $date
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function date($column, $date, $boolean = 'and', $not = false)
    {
        return $this->where('date('.$column.') = ?', $date, $boolean, $not);
    }

    /**
     * Add a "where day" to the query.
     * @param  string  $column
     * @param  string  $day
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function day($column, $day, $boolean = 'and', $not = false)
    {
        return $this->where_assoc(array('day('.$column.')' => $day), $boolean, $not);
    }

    /**
     * Add a "where month" to the query.
     * @param  string  $column
     * @param  string  $month
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function month($column, $month, $boolean = 'and', $not = false)
    {
        return $this->where_assoc(array('month('.$column.')' => $month), $boolean, $not);
    }

    /**
     * Add a "where date" to the query.
     * @param  string  $column
     * @param  string  $year
     * @param  string  $boolean
     * @param  boolean $not
     * @return Query
     */
    public function year($column, $year, $boolean = 'and', $not = false)
    {
        return $this->where_assoc(array('year('.$column.')' => $year), $boolean, $not);
    }

    /**
     * Alias of "insert"
     * @param  array $rows
     * @param  Query $query
     * @return Query
     */
    public function create(array $rows, Query $query = null)
    {
        return $this->insert($rows, $query);
    }

    /**
     * Alias of "execute"
     * @return mixed
     */
    public function exec(array $options = array())
    {
        return $this->execute($options);
    }

    /**
     * Alias of "execute"
     * @return mixed
     */
    public function run(array $options = array())
    {
        return $this->execute($options);
    }

    /**
     * Set fetch mode as assoc
     * @return Query
     */
    public function as_assoc() {
        $this->fetch_mode = 'assoc';
        return $this;
    }

    /**
     * Set fetch mode as object
     * @return Query
     */
    public function as_object() {
        $this->fetch_mode = 'object';
        return $this;
    }

    ######################################################
    ########### CALCULATIONS ##################################
    ######################################################

    /**
     * Return the "count" result of the query
     * @param  string $columns
     * @return int
     */
    public function count($columns = '*')
    {
        return (int) $this->select('count('.$columns.') count')->value('count');
    }

    /**
     * Return the max value of a column
     * @param  string $column
     * @return mixed
     */
    public function max($column)
    {
        return $this->select('max('.$column.') max')->value('max');
    }

    /**
     * Return the min value of a column
     * @param  string $column
     * @return mixed
     */
    public function min($column)
    {
        return $this->select('min('.$column.') min')->value('min');
    }

    /**
     * Return the sum of a column
     * @param  string $column
     * @return mixed
     */
    public function sum($column)
    {
        $rs = $this->select('sum('.$column.') sum')->value('sum');
        return $rs ?: 0;
    }

    ######################################################
    ########### EXECUTORS ################################
    ######################################################

    /**
     * Find a record by its primary key.
     * @param  mixed $ids
     * @return mixed
     */
    public function find($id, $primary_key = 'id') {
        $this->where(array($primary_key => $id));

        if (!is_array($id)) {
            return $this->first();
        }

        return $this->execute();
    }

    /**
     * Find the first record matching the conditions.
     * @param  mixed  $query
     * @param  mixed  $params
     * @return mixed
     */
    public function find_by($query, $params = array())
    {
        return $this->where($query, $params)->first();
    }

    /**
     * Alias of "limit" and run the query.
     * @param  int $limit
     * @return mixed
     */
    public function first($limit = 1)
    {
        $rs = $this->limit($limit);

        if ($limit == 1) {
            $this->type = 'select_one';
        }

        return $this->execute();
    }

    /**
     * Get a value of first record.
     * @param  string $column
     * @return mixed
     */
    public function value($column)
    {
        $rs = (array) $this->first();
        return $rs[$column];
    }

    /**
     * Get a values array
     * @param  string  $column
     * @param  string  $key
     * @param  boolean $collapse
     * @return array
     */
    public function pluck($column, $key = null, $collapse = false)
    {
        $columns = ($key == null) ? array($column) : array($column, $key);

        if (!$this->columns) {
            $this->select($columns);
        }

        $rs = $this->execute();

        $column = $this->column_basename($column);
        $key    = $this->column_basename($key) ?: $key;

        return array_pluck($rs, $column, $key, $collapse);
    }

    ######################################################
    ########### HELPERS ##################################
    ######################################################

    /**
     * Get SQL statement into string
     * @return string
     */
    public function to_sql() {
        if ($this->sql == null) {
            $this->sql = $this->builder->{$this->type}($this);
        }

        return $this->sql;
    }

    /**
     * Get column names from table
     * @return array
     */
    public function get_column_names()
    {
        if (array_get(static::$tables, $this->from)) {
            return static::$tables[$this->from]['columns'];
        }

        $database = $this->db->get_connection()->get_database();

        $table = $this->from;

        $query = new Query();
        return $query->from('INFORMATION_SCHEMA.COLUMNS')
            ->where(array(
                'table_schema' => $database,
                'table_name' => $table
            ))
            ->pluck('column_name');
    }

    /**
     * Get column base name.
     * @param  string $column
     * @return string
     */
    public function column_basename($column)
    {
        $name = explode('.', $column);
        return end($name);
    }

    /**
     * Valid if actual query is a select statement
     * @return boolean
     */
    public function is_select()
    {
        return strpos($this->type, 'select') !== false;
    }

    /**
     * Reset all binds list
     * @param  string $key
     * @return array
     */
    public function reset_binds( $key = null )
    {
        if ($key == null) {
            $this->binds = array();
        }

        else {
            $this->binds[ $key ] = array();
        }

        return $this->binds;
    }

    /**
     * Clean all elements of the binds.
     */
    public function clean_binds()
    {
        foreach ($this->binds as &$value) {
            $value = array();
        }
    }

    /**
     * Get an/all element(s) from binds list
     * @param  string $key
     * @return array
     */
    public function get_binds($key = null)
    {
        if ($key == null) {
            return $this->binds;
        }

        return $this->binds[ $key ];
    }

    /**
     * Get all values from binds list
     * @return array
     */
    public function get_binds_values()
    {
        return array_values_recursive_with_keys($this->binds);
    }

    /**
     * Rewrite an/all element(s) from binds list
     * @param  array  $binds
     * @param  string $key
     * @return array
     */
    public function set_binds($binds, $key = null)
    {
        if ($key == null) {
            $this->binds = $binds;
        }

        else {
            $this->binds[$key] = $binds;
        }

        return $this->binds;
    }

    /**
     * Add a new bind param to the query
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function add_bind($key, $value)
    {
        $this->binds[$key] = array_merge($this->binds[$key],(array) $value);

        return $this->binds;
    }

    /**
     * Allows treat the object as a string
     * @return string
     */
    public function __toString() {
        return $this->to_sql();
    }
}
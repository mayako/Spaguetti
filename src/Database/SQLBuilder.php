<?php

namespace Spaguetti\Database;

class SQLBuilder
{

    /**
     * List of methods to build a "select".
     * @var array
     */
    public $select = array(
        'columns',
        'from',
        'join',
        'where',
        'group',
        'having',
        'order',
        'limit',
        'offset'
    );

    /**
     * List of methods to build an "update"
     * @var array
     */
    public $update = array(
        'where',
        'order',
        'limit'
    );

    /**
     * List of methods to build an "update"
     * @var array
     */
    public $delete = array(
        'from',
        'where',
        'order',
        'limit'
    );

    /**
    * Build a "select".
    * @param  Query $query
    * @return string
    */
    public function select(Query $query)
    {
        $sql = array();

        if ($query->columns == null) {
            $query->columns = array('*');
        }

        foreach ($this->select as $method) {

            if (!empty($query->$method) and method_exists($this, $method)) {
                $sql[] = $this->$method( $query->$method );
            }

        }

        return implode(' ', $sql);
    }

    /**
     * Alias of "select"
     * @param  Query  $query
     * @return string
     */
    public function select_one(Query $query)
    {
        return $this->select($query);
    }

    /**
     * Set the columns to be selected
     * @param  array $columns
     * @return string
     */
    public function columns($columns)
    {
        return 'select '.implode(',', $columns);
    }

    /**
     * Set table on which the query will be executed
     * @param  string $table
     * @return string
     */
    public function from($table)
    {
        return 'from ' . $table;
    }

    /**
     * Set a "join" to the query.
     * @param  array $joins
     * @return string
     */
    public function join($joins)
    {
        $sql = array();
        foreach ($joins as $join) {

            if (isset($join['query'])) {
                $sql[] = $join['query'];
            }

            else {
                $sql[] = implode(' ', array(
                    $join['type'],
                    'join',
                    $join['table'],
                    'on',
                    $join['column_one'],
                    '=',
                    $join['column_two']
                ));
            }
        }
        return implode(' ', $sql);
    }

    /**
     * Add a "where" to the query
     * @param  array $conditions
     * @return string
     */
    public function where($conditions)
    {
        $sql = array();

        foreach ($conditions as $condition) {
            $method = 'where_'.$condition['type'];

            $boolean = count($sql) > 0 ? $condition["boolean"].' ' : '';

            $sql[] = $boolean . call_user_func_array(array($this, $method), $condition);
        }

        return 'where '.implode(" ", $sql);
    }

    /**
     * Add a new condition with bind params
     * @param  string $query
     * @param  array $params
     * @param  string $boolean
     * @param  boolean $not
     * @return string
     */
    public function where_bind($query, $params, $boolean, $not) {
        return $not ? 'not ('.$query.')' : $query;
    }

    /**
     * Add a new condition with syntax column => value
     * @param  string $column
     * @param  mixed $values
     * @param  string $boolean
     * @param  boolean $not
     * @return string
     */
    public function where_assoc($column, $values, $boolean, $not)
    {

        if( $values === null ) {
            $operator = $not ? 'is not' : 'is';
            $value = 'null';
        }

        else {
            $value = implode(',', array_fill(0, count($values), '?'));

            if( is_array($values) ) {
                $operator = $not ? 'not in' : 'in';
                $value = '('.$value.')';
            }

            else {
                $operator = $not ? '<>' : '=';
            }
        }
        return implode(' ', compact('column', 'operator', 'value'));
    }

    /**
     * Add a sub-query condition
     * @param  string $column
     * @param  Query $query
     * @param  string $boolean
     * @param  boolean $not
     * @return string
     */
    public function where_sub($column, $query, $boolean, $not)
    {
        $operator = $not ? 'not in' : 'in';
        $sql = $query->to_sql();
        return $column.' '.$operator.'('.$sql.')';
    }

    public function exists(Query $query)
    {
        $select = $this->select($query);

        return 'select exists('.$select.') as exist';
    }

    /**
     * Add a "group by" to the query
     * @param  array $columns
     * @return string
     */
    public function group($columns)
    {
        return 'group by '.implode(',', $columns);
    }

    /**
     * Add a "having" to the query
     * @param  array $conditions
     * @return string
     */
    public function having($conditions)
    {
        $sql = array();
        foreach ($conditions as $condition) {

            $boolean = count($sql) > 0 ? 'and ' : '';

            $sql[] = $boolean.$condition['query'];
        }

        return 'having '.implode(' ', $sql);
    }

    /**
     * Add an "order by" to the query
     * @param  array $orders
     * @return string
     */
    public function order($orders)
    {
        return 'order by '.implode(',', array_map(function($order) {
            if (is_array($order)) {
                return implode(' ', $order);
            }
            return $order;
        }, $orders));
    }

    /**
     * Add a "limit" to the query
     * @param  int $limit
     * @return string
     */
    public function limit($limit)
    {
        return 'limit '.$limit;
    }

    /**
     * Add an "offset" to the query
     * @param  int $offset
     * @return string
     */
    public function offset( $offset )
    {
        return 'offset '.$offset;
    }

    /**
     * Build an "insert" statement
     * @param  Query  $query
     * @return string
     */
    public function insert(Query $query)
    {
        if ($query->rows instanceof Query) {
            return 'insert into '.$query->from.'('.implode(',', $query->columns).') '.$query->rows;
        }

        $values = array();
        foreach ($query->rows as $row) {
            $values[] = '('.implode(',', array_fill(0, count($row), '?')).')';
        }

        return 'insert into '.$query->from.'('.implode(',', $query->columns).') values'.implode(',', $values);
    }

    /**
     * Build an single "insert" statement
     * @param  Query  $query
     * @return string
     */
    public function insert_get_id(Query $query)
    {
        $values = implode(',', array_fill(0, count($query->rows), '?'));
        return 'insert into '.$query->from.'('.implode(',', $query->columns).') values('.$values.')';
    }

    /**
     * Build an "update" statement
     * @param  Query  $query
     * @return string
     */
    public function update(Query $query)
    {
        $sql = array();

        $sql[] = 'update '.$query->from;

        $sql[] = $this->set($query->rows);

        foreach ($this->update as $method) {
            if ($query->$method and method_exists($this, $method)) {
                $sql[] = $this->$method($query->$method);
            }
        }

        return implode(' ', $sql);
    }

    /**
     * Build a "delete" statement
     * @param  Query  $query
     * @return string
     */
    public function delete(Query $query)
    {
        $sql = array();

        foreach ($this->delete as $method) {
            if ($query->$method and method_exists($this, $method)) {
                $sql[] = $this->$method($query->$method);
            }
        }

        return 'delete '.implode(' ', $sql);
    }

    /**
     * Add a "set" statement
     * @param array $row
     */
    public function set($row)
    {
        $columns = array_fill_keys(array_keys($row), '?');

        return 'set '.implode(',', array_map_with_keys($columns, function($value, $column) {
            return $column.' = '.$value;
        }));
    }

}
<?php namespace Graphist\Database;

class Query {

	public $connection;
	public $syntax;
	
	public $selects;
	public $columns;
	public $from;
	public $wheres;
	public $groupings;
	public $orderings;
	public $limit;

	public $bindings = array();

	public function __construct(Connection $connection, $table)
	{
		$this->connection = $connection;
		$this->from = $table;

		$this->syntax = new Syntax($this->connection);
	}

	public function insert($data)
	{
		if (!empty($data))
		{
			$sql = $this->syntax->insert($this, $data);

			$bindings = array_values($data);

			return $this->connection->query($sql, $bindings);
		}
	}

	public function select($columns = array("*"))
	{
		$this->selects = (array) $columns;
		return $this;
	}

	public function where($column, $condition, $value, $connector = "AND")
	{	
		$type = "WHERE";

		$this->wheres[] = compact('type', 'column', 'condition', 'value', 'connector');
		$this->bindings[] = $value;

		return $this;
	}

	public function or_where($column, $condition, $value)
	{
		$this->where($column, $condition, $value, "OR");
		return $this;
	}

	public function and_where($column, $condition, $value)
	{
		$this->where($column, $condition, $value, "AND");
		return $this;
	}

	public function group_by($column)
	{
		$this->groupings[] = $column;
		return $this;
	}

	public function order_by($column, $direction = "ASC")
	{
		$this->orderings[] = compact('column', 'direction');
		return $this;
	}

	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	public function get($columns = array("*"))
	{
		if (is_null($this->selects)) $this->select($columns);

		$sql = $this->syntax->select($this);

		$results = $this->connection->query($sql, $this->bindings);

		$this->selects = null;

		return $results;
	}

	public function update($data)
	{
		if (!empty($data))
		{
			$sql = $this->syntax->update($this, $data);

			$rowCount = $this->connection->query($sql, $this->bindings);

			return $rowCount;
		}
	}

	public function delete()
	{
		$sql = $this->syntax->delete($this);

		$rowCount = $this->connection->query($sql, $this->bindings);

		return $rowCount;
	}

	public function count($column = "*")
	{
		$sql = $this->syntax->count($this, $column);

		return (int) $this->connection->only($sql, $this->bindings);
	}

}
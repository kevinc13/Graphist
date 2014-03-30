<?php namespace Graphist\Database;

class Syntax {

	public $connection;

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	public function insert(Query $query, $data)
	{
		$sql = "INSERT INTO {$query->from} (";

		$sql .= implode(",", array_keys($data));
		$sql .= ") VALUES (";

		$placeholders = "";
		for ($i = 0; $i < count($data); $i++) 
		{ 
			if ( (count($data) - 1) === $i )
			{	
				$placeholders .= "?";
			}
			else
			{
				$placeholders .= "? , ";
			}
		}

		$sql .= $placeholders;
		$sql .= ")";

		return $sql;
	} 

	public function update(Query $query, $data)
	{
		$sql = "UPDATE {$query->from} SET ";
		
		$i = 0;	
		foreach ($data as $column => $value) 
		{	
			if ( $i < (count($data) - 1) )
			{
				$sql .= "{$column} = '{$value}', ";
			}
			else
			{
				$sql .= "{$column} = '{$value}'";
			}

			$i++;
		}

		if (!empty($query->wheres))
		{
			$sql .= " WHERE ";

			foreach ($query->wheres as $value) 
			{
				$sql .= "{$value['connector']} {$value['column']} {$value['condition']} ?";
			}
		}

		return $sql;
	}	

	public function select(Query $query)
	{
		$sql = "SELECT ";
		$sql .= implode(",", $query->selects);
		$sql .= " FROM {$query->from}";

		if (!empty($query->wheres))
		{
			$sql .= " WHERE ";

			foreach ($query->wheres as $value) 
			{
				$sql .= "{$value['connector']} {$value['column']} {$value['condition']} ?";
			}
		}

		if (!empty($query->groupings))
		{
			$sql .= " GROUP BY {$query->groupings[0]}";
		}

		if (!empty($query->orderings))
		{
			$sql .= " ORDER BY ";

			for ($i = 0; $i < count($query->orderings); $i++) 
			{ 
				if ( (count($query->orderings) - 1) == $i )
				{
					$sql .= "{$query->orderings[$i]['column']} {$query->orderings[$i]['direction']}";
				}
				else
				{
					$sql .= "{$query->orderings[$i]['column']} {$query->orderings[$i]['direction']},";
				}
			}
		}

		if (!empty($query->limit))
		{
			$sql .= " LIMIT {$query->limit}";
		}

		return $sql;
	}

	public function delete(Query $query)
	{
		$sql = "DELETE FROM " . $query->from;
		$sql .= " WHERE ";

		if (!empty($query->wheres))
		{
			foreach ($query->wheres as $value) {
				$sql .= "{$value['connector']} {$value['column']} {$value['condition']} ?";
			}
		}

		return $sql;
	}

	public function count(Query $query, $column)
	{
		$sql = "SELECT COUNT({$column}) FROM {$query->from}";

		if (!empty($query->wheres))
		{
			$sql .= " WHERE ";
			foreach ($query->wheres as $value) {
				$sql .= "{$value['connector']} {$value['column']} {$value['condition']} ?";
			}
		}

		return $sql;
	}

}
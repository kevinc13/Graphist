<?php namespace Graphist\Database;

class Connection {

	public $pdo;

	public function __construct(\PDO $pdo) 
	{
		$this->pdo = $pdo;
	}

	public function table($table)
	{
		return new Query($this, $table);
	}

	public function query($sql, $bindings = array()) 
	{
		$sql = trim($sql);

		list($statement, $result) = $this->execute($sql, $bindings);

		if (stripos($sql, "select") === 0) {

			return $this->fetch($statement);

		} elseif (stripos($sql, 'update') === 0 or stripos($sql, 'delete') === 0) {

			return $statement->rowCount();

		} elseif (stripos($sql, "insert") === 0) {

			return $statement->rowCount();

		} else {

			return $result;

		}
	}

	public function only($sql, $bindings = array())
	{
		$sql = trim($sql);

		list($statement, $result) = $this->execute($sql, $bindings);

		return $statement->fetchColumn();
	}

	public function execute($sql, $bindings = array()) 
	{
		try {

			$statement = $this->pdo->prepare($sql);

			if (!empty($bindings)) {

				/*foreach ($bindings as $param => $value) {
					$statement->bindParam($param, $value);
				}*/

				$result = $statement->execute($bindings);

			} else {

				$result = $statement->execute();

			}
			
		} catch (\PDOException $e) {
			throw $e;
		}

		return array($statement, $result);
	}

	public function fetch($statement) 
	{
		try {

			return $statement->fetchAll(\PDO::FETCH_ASSOC);

		} catch (\PDOException $e) {
			throw $e;
		}
	}

	public function end()
	{
		$this->pdo = null;
	}

}
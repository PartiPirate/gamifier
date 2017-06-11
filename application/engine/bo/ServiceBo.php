<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

	This file is part of Gamifier.

    Gamifier is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Gamifier is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Gamifier.  If age, see <http://www.gnu.org/licenses/>.
*/

class ServiceBo {
	var $pdo = null;

	var $TABLE = "services";
	var $ID_FIELD = "ser_id";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new ServiceBo($pdo, $config);
	}

	function create(&$service) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$service[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($service) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($service as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $service);

		$statement = $this->pdo->prepare($query);
		$statement->execute($service);
	}

	function save(&$service) {
 		if (!isset($service[$this->ID_FIELD]) || !$service[$this->ID_FIELD]) {
			$this->create($service);
		}

		$this->update($service);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByUUIDSecret($uuid, $secret) {
		$filters = array("ser_uuid" => $uuid, "ser_secret" => $secret);

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$query = "	SELECT *
					FROM $this->TABLE
					WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
		}

		if (isset($filters["ser_uuid"])) {
			$args["ser_uuid"] = $filters["ser_uuid"];
			$query .= " AND ser_uuid = :ser_uuid \n";
		}

		if (isset($filters["ser_secret"])) {
			$args["ser_secret"] = $filters["ser_secret"];
			$query .= " AND ser_secret = :ser_secret \n";
		}

//		$query .= "	ORDER BY ser_parent_id ASC , ser_order ASC ";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$results = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $index => $line) {
				foreach($line as $field => $value) {
					if (is_numeric($field)) {
						unset($results[$index][$field]);
					}
				}
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $results;
	}
}
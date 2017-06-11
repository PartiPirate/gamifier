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

class BadgeLogBo {
	var $pdo = null;

	var $TABLE = "badge_logs";
	var $ID_FIELD = "blo_id";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new BadgeLogBo($pdo, $config);
	}

	function create(&$badgeLog) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$badgeLog[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($badgeLog) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($badgeLog as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $badgeLog);

		$statement = $this->pdo->prepare($query);
		$statement->execute($badgeLog);
	}

	function save(&$badgeLog) {
 		if (!isset($badgeLog[$this->ID_FIELD]) || !$badgeLog[$this->ID_FIELD]) {
			$this->create($badgeLog);
		}

		$this->update($badgeLog);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

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

//		if (isset($filters["blo_agenda_id"])) {
//			$args["blo_agenda_id"] = $filters["blo_agenda_id"];
//			$query .= " AND blo_agenda_id = :blo_agenda_id \n";
//		}

//		$query .= "	ORDER BY blo_parent_id ASC , blo_order ASC ";

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
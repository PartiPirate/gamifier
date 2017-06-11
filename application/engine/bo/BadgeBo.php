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

class BadgeBo {
	var $pdo = null;

	var $TABLE = "badges";
	var $ID_FIELD = "bad_id";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new BadgeBo($pdo, $config);
	}

	function create(&$badge) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$badge[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($badge) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($badge as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $badge);

		$statement = $this->pdo->prepare($query);
		$statement->execute($badge);
	}

	function save(&$badge) {
 		if (!isset($badge[$this->ID_FIELD]) || !$badge[$this->ID_FIELD]) {
			$this->create($badge);
		}

		$this->update($badge);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByUserBadge($userUuid, $badgeUuid, $serviceId) {
		$filters = array("bru_service_id" => $serviceId, "bru_uuid" => $badgeUuid, "bad_user_uuid" => $userUuid);

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
					JOIN badge_rules ON bru_id = bad_badge_rule_id
					WHERE
						1 = 1 \n";

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$query .= " AND $this->ID_FIELD = :$this->ID_FIELD \n";
		}
		
		if (isset($filters["bru_service_id"])) {
			$args["bru_service_id"] = $filters["bru_service_id"];
			$query .= " AND bru_service_id = :bru_service_id \n";
		}
		
		if (isset($filters["bru_uuid"])) {
			$args["bru_uuid"] = $filters["bru_uuid"];
			$query .= " AND bru_uuid = :bru_uuid \n";
		}

		if (isset($filters["bad_user_uuid"])) {
			$args["bad_user_uuid"] = $filters["bad_user_uuid"];
			$query .= " AND bad_user_uuid = :bad_user_uuid \n";
		}

//		$query .= "	ORDER BY bad_parent_id ASC , bad_order ASC ";

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
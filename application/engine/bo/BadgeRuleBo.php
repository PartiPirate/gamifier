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

class BadgeRuleBo {
	var $pdo = null;

	var $TABLE = "badge_rules";
	var $ID_FIELD = "bru_id";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new BadgeRuleBo($pdo, $config);
	}

	function create(&$badgeRule) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$badgeRule[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($badgeRule) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($badgeRule as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $badgeRule);

		$statement = $this->pdo->prepare($query);
		$statement->execute($badgeRule);
	}

	function save(&$badgeRule) {
 		if (!isset($badgeRule[$this->ID_FIELD]) || !$badgeRule[$this->ID_FIELD]) {
			$this->create($badgeRule);
		}

		$this->update($badgeRule);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}
	
	function getByEventTrigger($eventId) {
		$filters = array("like_trigger" => "%\"$eventId\"%");
		
		return $this->getByFilters($filters);
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

		if (isset($filters["like_trigger"])) {
			$args["like_trigger"] = $filters["like_trigger"];
			$query .= " AND bru_event_triggers LIKE :like_trigger \n";
		}

		if (isset($filters["bru_service_id"])) {
			$args["bru_service_id"] = $filters["bru_service_id"];
			$query .= " AND bru_service_id = :bru_service_id \n";
		}

//		$query .= "	ORDER BY bru_parent_id ASC , bru_order ASC ";

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

	function countEvents($eventUUID, $userUUID) {
		$query = "	SELECT COUNT(blo_id) AS count_blo_id 
					FROM badge_logs WHERE
						1 = 1 \n";

		$args["blo_event_uuid"] = $eventUUID;
		$query .= " AND blo_event_uuid LIKE :blo_event_uuid \n";

		$args["blo_user_uuid"] = $userUUID;
		$query .= " AND blo_user_uuid LIKE :blo_user_uuid \n";

//		$query .= "	ORDER BY bru_parent_id ASC , bru_order ASC ";

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

		if (count($results)) return $results[0]["count_blo_id"];
		
		return 0;
	}

	function countBadges($ruleid, $userUUID) {
		$query = "	SELECT COUNT(bad_id) AS count_bad_id 
					FROM badges WHERE
						1 = 1 \n";

		$args["bad_badge_rule_id"] = $ruleid;
		$query .= " AND bad_badge_rule_id = :bad_badge_rule_id \n";

		$args["bad_user_uuid"] = $userUUID;
		$query .= " AND bad_user_uuid = :bad_user_uuid \n";

//		$query .= "	ORDER BY bru_parent_id ASC , bru_order ASC ";

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

//		print_r($results);

		if (count($results)) return $results[0]["count_bad_id"];
		
		return 0;
	}
	
	function test($rule, $userUUID) {
//		echo "Test $userUUID on rule " . $rule["bru_label"] . "\n";

		$globalRule = json_decode($rule["bru_rule"], true);

//		print_r($globalRule);

		$test = true;

		$numberOfBadges = $this->countBadges($rule["bru_id"], $userUUID);

		if ($numberOfBadges >= $globalRule["number_of_badges"]) {
			$test = false;
		}

//		echo "\tNot enough badge : " . $test . "\n";

		if ($test) {
			foreach($globalRule["conditions"] as $condition) {
				$numberOfEvents = $this->countEvents($condition["event_uuid"], $userUUID);
				
//				echo "\tNumber of events : " . $numberOfEvents . "\n";

				if ($condition["condition"] == "equal") {
					if (($numberOfEvents % $condition["value"]) != 0) {
						$test = false;
						break;
					}
				}
			}
		}

//		echo "\tResult : " . $test . "\n";
		
		return $test;
	}
}
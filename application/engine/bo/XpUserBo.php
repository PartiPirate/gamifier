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

class XpUserBo {
	var $pdo = null;

	var $TABLE = "xp_users";
	var $ID_FIELD = "xus_id";

	function __construct($pdo, $config) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new XpuserBo($pdo, $config);
	}

	function create(&$xpUser) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$xpUser[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($xpUser) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($xpUser as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $xpUser);

		$statement = $this->pdo->prepare($query);
		$statement->execute($xpUser);
	}

	function save(&$xpUser) {
 		if (!isset($xpUser[$this->ID_FIELD]) || !$xpUser[$this->ID_FIELD]) {
			$this->create($xpUser);
		}

		$this->update($xpUser);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByUUID($id) {
		$filters = array("xus_user_uuid" => $id);

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

		if (isset($filters["xus_user_uuid"])) {
			$args["xus_user_uuid"] = $filters["xus_user_uuid"];
			$query .= " AND xus_user_uuid = :xus_user_uuid \n";
		}

//		$query .= "	ORDER BY xus_parent_id ASC , xus_order ASC ";

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
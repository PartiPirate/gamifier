<?php /*
	Copyright 2017 Cédric Levieux, Parti Pirate

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

include_once("config/database.php");
require_once("engine/bo/ServiceBo.php");
require_once("engine/bo/EventDefinitionBo.php");
require_once("engine/bo/BadgeRuleBo.php");

if (!isset($api)) exit();

$connection = openConnection();

$serviceBo = ServiceBo::newInstance($connection, $config);
$eventDefinitionBo = EventDefinitionBo::newInstance($connection, $config);
$badgeRuleBo = BadgeRuleBo::newInstance($connection, $config);

$response["data"]["badges"] = array();
$response["data"]["events"] = array();

if (!isset($arguments["service_uuid"])) $response["errors"][] = "no_service_uuid";
if (!isset($arguments["service_secret"])) $response["errors"][] = "no_service_secret";

if (isset($arguments["service_uuid"]) && isset($arguments["service_secret"])) {
    $service = $serviceBo->getByUUIDSecret($arguments["service_uuid"], $arguments["service_secret"]);

    if (!$service) {
        $response["errors"][] = "bad_authentication";
    }
    else {
        $arguments["service_id"] = $service["ser_id"];
    }
}

if (count($response["errors"])) return;

$badgeRules = $badgeRuleBo->getByFilters(array("bru_service_id" => $arguments["service_id"]));
$events = $eventDefinitionBo->getByFilters(array("ede_service_id" => $arguments["service_id"]));

foreach($badgeRules as $badgeRuleDB) {
    $response["data"]["badges"][] = array("label" => utf8_encode($badgeRuleDB["bru_label"]), "description" => utf8_encode($badgeRuleDB["bru_description"]), "uuid" => $badgeRuleDB["bru_uuid"], "rule" => $badgeRuleDB["bru_rule"]);
}

foreach($events as $eventDB) {
    $response["data"]["events"][] = array("label" => utf8_encode($eventDB["ede_label"]), "uuid" => $eventDB["ede_uuid"]);
}

$response["status"] = "OK";

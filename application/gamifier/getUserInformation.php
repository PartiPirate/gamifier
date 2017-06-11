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
require_once("engine/bo/BadgeBo.php");
require_once("engine/bo/XpUserBo.php");

if (!isset($api)) exit();

$connection = openConnection();

$serviceBo = ServiceBo::newInstance($connection, $config);
$badgeBo = BadgeBo::newInstance($connection, $config);
$xpUserBo = XpUserBo::newInstance($connection, $config);

$response["data"]["badges"] = array();

// print_r($arguments);

if (!isset($arguments["user_uuid"])) $response["errors"][] = "no_user_uuid";
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

$filters = array();
$filters["bru_service_id"] = $arguments["service_id"];
$filters["bad_user_uuid"] = $arguments["user_uuid"];

$badges = $badgeBo->getByFilters($filters);

foreach($badges as $badgeDB) {
    $response["data"]["badges"][] = array("label" => utf8_encode($badgeDB["bru_label"]), "description" => utf8_encode($badgeDB["bru_description"]), "uuid" => $badgeDB["bru_uuid"], "noticed" => $badgeDB["bad_noticed"] ? true : false, "rule" => $badgeDB["bru_rule"]);
}

$xpUser = $xpUserBo->getByUUID($arguments["user_uuid"]);

if (!$xpUser) {
    $xpUser = array("xus_user_uuid" => $arguments["user_uuid"], "xus_xp" => 0);
}

$response["data"]["xp"] = $xpUser["xus_xp"];
$response["data"]["uuid"] = $xpUser["xus_user_uuid"];

$response["status"] = "OK";

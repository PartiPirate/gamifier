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

// print_r($arguments);

if (!isset($arguments["notices"]) || !count($arguments["notices"])) {
    $response["errors"][] = "no_notices";
}

if (count($response["errors"])) return;

$notices = $arguments["notices"];

foreach($notices as $index => $notice) {
    if (!isset($notice["user_uuid"])) $response["errors"][] = array("data" => $notice, "no_user_uuid");
    if (!isset($notice["badge_uuid"])) $response["errors"][] = array("data" => $notice, "no_badge_uuid");
    if (!isset($notice["service_uuid"])) $response["errors"][] = array("data" => $notice, "no_service_uuid");
    if (!isset($notice["service_secret"])) $response["errors"][] = array("data" => $notice, "no_service_secret");

    if (isset($notice["service_uuid"]) && isset($notice["service_secret"])) {
        $service = $serviceBo->getByUUIDSecret($notice["service_uuid"], $notice["service_secret"]);
    
        if (!$service) {
            $response["errors"][] = array("data" => $notice, "bad_authentication");
        }
        else {
            $badge = $badgeBo->getByUserBadge($notice["user_uuid"], $notice["badge_uuid"], $service["ser_id"]);

            if (!$badge) {
                $response["errors"][] = array("data" => $notice, "no_notice");
            }
            else {
                $notices[$index]["badge_id"] = $badge["bad_id"];
            }
        }
    }
}

if (count($response["errors"])) return;

foreach($notices as $index => $notice) {
    $badgeDB = array("bad_id" => $notice["badge_id"], "bad_noticed" => 1);
    $badgeBo->save($badgeDB);
}

$response["status"] = "OK";

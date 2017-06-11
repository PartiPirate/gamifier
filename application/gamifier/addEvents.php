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
require_once("engine/bo/BadgeLogBo.php");
require_once("engine/bo/BadgeRuleBo.php");
require_once("engine/bo/BadgeBo.php");
require_once("engine/bo/XpUserBo.php");

if (!isset($api)) exit();

$connection = openConnection();

$serviceBo = ServiceBo::newInstance($connection, $config);
$eventDefinitionBo = EventDefinitionBo::newInstance($connection, $config);
$badgeLogBo = BadgeLogBo::newInstance($connection, $config);
$badgeRuleBo = BadgeRuleBo::newInstance($connection, $config);
$badgeBo = BadgeBo::newInstance($connection, $config);
$xpUserBo = XpUserBo::newInstance($connection, $config);

$response["data"]["badges"] = array();
$response["data"]["users"] = array();

// print_r($arguments);

if (!isset($arguments["events"]) || !count($arguments["events"])) {
    $response["errors"][] = "no_events";
}

if (count($response["errors"])) return;

$events = $arguments["events"];

foreach($events as $index => $event) {
    if (!isset($event["user_uuid"])) $response["errors"][] = array("data" => $event, "no_user_uuid");
    if (!isset($event["event_uuid"])) $response["errors"][] = array("data" => $event, "no_event_uuid");
    if (!isset($event["service_uuid"])) $response["errors"][] = array("data" => $event, "no_service_uuid");
    if (!isset($event["service_secret"])) $response["errors"][] = array("data" => $event, "no_service_secret");

    if (isset($event["service_uuid"]) && isset($event["service_secret"])) {
        $service = $serviceBo->getByUUIDSecret($event["service_uuid"], $event["service_secret"]);
    
        if (!$service) {
            $response["errors"][] = array("data" => $event, "bad_authentication");
        }
        else {
            $event_definition = $eventDefinitionBo->getByUUIDService($event["event_uuid"], $service["ser_id"]);
        
            if (!$event_definition) {
                $response["errors"][] = array("data" => $event, "no_event");
            }
            else {
                $events[$index]["event_id"] = $event_definition["ede_id"];
                $events[$index]["event_xp"] = $event_definition["ede_xp"] ? $event_definition["ede_xp"] : 0;
            }
        }
    }
}

if (count($response["errors"])) return;

$now = getNow();
$now = $now->format("Y-m-d H:i:s");

foreach($events as $index => $event) {
    
    $xpUser = $xpUserBo->getByUUID($event["user_uuid"]);
    
    if (!$xpUser) {
        $xpUser = array("xus_user_uuid" => $event["user_uuid"], "xus_xp" => 0);
    }
    
    $eventDB = array();
    $eventDB["blo_user_uuid"] = $event["user_uuid"];
    $eventDB["blo_event_uuid"] = $event["event_uuid"];
    $eventDB["blo_datetime"] = $now;

    $xpUser["xus_xp"] += $event["event_xp"];

    $badgeLogBo->save($eventDB);
    
    $rules = $badgeRuleBo->getByEventTrigger($event["event_id"]);

    foreach($rules as $rule) {
        $test = $badgeRuleBo->test($rule, $event["user_uuid"]);
        
        if ($test) {
            $badgeDB = array();
            $badgeDB["bad_user_uuid"] = $event["user_uuid"];
            $badgeDB["bad_badge_rule_id"] = $rule["bru_id"];
            $badgeDB["bad_datetime"] = $now;

            $badgeBo->save($badgeDB);

            $xpUser["xus_xp"] += $rule["bru_xp"] ? $rule["bru_xp"] : 0;
            
            $response["data"]["badges"][] = array("label" => utf8_encode($rule["bru_label"]), "uuid" => $rule["bru_uuid"], "rule" => $rule["bru_rule"], "noticed" => false);
        }
    }

    if ($xpUser["xus_xp"]) {
        $xpUserBo->save($xpUser);
        
        $response["data"]["users"][$xpUser["xus_user_uuid"]] = array("xp" => $xpUser["xus_xp"], "uuid" => $xpUser["xus_user_uuid"]);
    } 
}

$response["status"] = "OK";

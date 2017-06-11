<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

	This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config/database.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/FormUtils.php");

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$response = array("status" => "KO", "errors" => array(), "data" => array());

if (!isset($_GET["method"]) && !isset($_POST["method"])) {
    $response["errors"][] =" no_method_given";
}
else {
    if (isset($_GET["method"])) {
        $method = $_GET["method"];
    }
    else {
        $method = $_POST["method"];
    }
    
    error_log("Meeting API Method : $method");
    // Security
    
    if (strpos($method, "..") !== false) {
    	echo json_encode(array("error" => "not_a_service"));
    	exit();
    }
    
    if (!file_exists("gamifier/$method.php")) {
    	echo json_encode(array("error" => "not_a_service"));
    	exit();
    }
    
//    print_r($_POST);
//    echo "\n";
    
    
    $arguments = $_POST["request"];

//    print_r($arguments);
//    echo "\n";

    $arguments = json_decode($arguments, true);

//    print_r($arguments);
//    echo "\n";
    
    $api = true;
    
    //require_once("engine/utils/LogUtils.php");
    //
    //// We don't log the get methods => A LOT OF THEM
    //if (strpos($method, "do_get") === false) {
    //	addLog($_SERVER, $_SESSION, $method, $arguments);
    //}
    
    //error_log(print_r($_POST, true));
    
    include("gamifier/$method.php");
}

echo json_encode($response);

?>
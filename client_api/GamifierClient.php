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

class GamifierClient {
	var $url = null;

	function __construct($pdo, $config) {
		$this->url = $url;
	}

	static function newInstance($url) {
		return new GamifierClient($url);
	}

    function _send($method, $request) {
        $getUrl = $this->url . "?method=$method";
        
		//url-ify the data for the POST
		$fieldsString = http_build_query($request);

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data, and say that we want the result returnd not printed
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($request));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		return $this->_exec($ch);
    }

	function _exec(&$ch) {
		// Execute request
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);

		// json decode the result, the api has json encoded result
		$result = json_decode($result, true);

		return $result;
	}
}
<?php
/**
 * Omnicoin plugin for MyBB
 * Copyright (c) 2014 by the Omnicoin Team.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Visit https://github.com/Omnicoin-Project/Omnicoin/wiki for more details
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'coins.php');

require_once "./global.php";

//global $mybb, $db, $templates, $headerinclude, $header, $footer, $username, $entries, $search, $username;


if($mybb->user['uid'] == 0)
{
	error_no_permission();
}

if (isset($mybb->input['action'])) {
	if ($mybb->input['action'] == "history") {
		if (isset($mybb->input['uid'])) {
			$uid = $mybb->input['uid']; 
		} else {
			$uid = $mybb->user[uid];
		}
		$uid = intval(preg_replace("/[^0-9]/", "", $uid));
			
		// get the username corresponding to the UID passed to the miscpage
		$grabuser = $db->simple_select("users", "username", "uid = '" . $uid . "'");
		$user = $db->fetch_array($grabuser);
		$username = $user['username'];
	
		//get all past addresses from table
		$query = $db->simple_select("omcaddresses", "address, date", "uid = '" . $uid . "'", array("order_by" => "date", "order_dir" => "ASC"));
		$entries = "";
			
		if ($query->num_rows > 0) {
			// loop through each row in the database that matches our query and create a table row to display it
			while($row = $db->fetch_array($query)){
				$address = $row['address'];
				$date = $row['date'];
				$template = $templates->get("Omnicoin Address History Entry");
				eval("\$entries .=\"" . $template . "\";");
			}
		} else {
			$message = "No address history";
			$template = $templates->get("Omnicoin Address History No Entry");
			eval("\$entries .=\"" . $template . "\";");
		}
		
		// grab our template
		$template = $templates->get("Omnicoin Address History");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	}
	
	else if ($mybb->input['action'] == "search") {
		if (isset($mybb->input['query'])) {
			$search = $db->escape_string(preg_replace("/[^A-Za-z0-9]/", "", $mybb->input['query']));
				
			//Get all addresses matching search term
			$query = $db->simple_select("omcaddresses", "address, date, uid", "address LIKE CONCAT('%', '" . $search . "', '%')", array("order_by" => "date", "order_dir" => "ASC"));
			$entries = "";
				
			if ($query->num_rows > 0) {
				//Loop through each row in the database that matches our query and create a table row to display it
				while($row = $db->fetch_array($query)){
					$grabuser = $db->simple_select("users", "username", "uid = '" . $row['uid'] . "'");
					$user = $db->fetch_array($grabuser);
						
					$username = $user['username'];
					$userid = $row['uid'];
					$address = $row['address'];
					$date = $row['date'];
					$template = $templates->get("Omnicoin Address Search Results Entry");
					eval("\$entries .=\"" . $template . "\";");
				}
			} else {
				$message = "No results found";
				$template = $templates->get("Omnicoin Address Search Results No Entry");
				eval("\$entries .=\"" . $template . "\";");
			}
				
			$template = $templates->get("Omnicoin Address Search Results");
			eval("\$page=\"" . $template . "\";");
			output_page($page);
		} else {			
			$template = $templates->get("Omnicoin Address Search");
			eval("\$page=\"" . $template . "\";");
			output_page($page);
		}
	}
}

?>

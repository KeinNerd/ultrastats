<?php
/*
	********************************************************************
	* Copyright by Andre Lorbach | 2006, 2007, 2008						
	* -> www.ultrastats.org <-											
	* ------------------------------------------------------------------
	*
	* Use this script at your own risk!									
	*
	* ------------------------------------------------------------------
	* ->	Search in Chat
	*		Helper to search for chat phrases
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/

// *** Default includes	and procedures *** //
define('IN_ULTRASTATS', true);
$gl_root_path = './';
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/functions_frontendhelpers.php');

InitUltraStats();
InitFrontEndDefaults();	// Only in WebFrontEnd
// ***					*** //

// --- BEGIN CREATE TITLE
$content['TITLE'] = InitPageTitle();

// Append custom title part!
$content['TITLE'] .= " :: Search in Chatlogs";
// --- END CREATE TITLE


// --- BEGIN Custom Code
if ( isset($_GET['search']) )
{
	// Get as number
	$content['searchfor'] = DB_RemoveBadChars($_GET['search']);
	
	if ( strlen($content['searchfor']) > 2 )
	{
		// Set SQL Query
		$sqlquery = " WHERE " . STATS_ALIASES . ".PLAYERID = " . $content['searchfor']; 

		// --- Read Chatlog ;)!
		$sqlquery = "SELECT " .
							STATS_ROUNDS . ".ID, " . 
							STATS_CHAT . ".PLAYERID, " . 
							STATS_CHAT . ".TextSaid " .
							" FROM " . STATS_CHAT . 
							" INNER JOIN (" . STATS_ROUNDS . 
							") ON (" . 
							STATS_ROUNDS . ".ID=" . STATS_CHAT . ".ROUNDID) " . 
							" WHERE " . STATS_CHAT . ".TextSaid LIKE '%" . $content['searchfor'] . "%' " . 
							GetBannedPlayerWhereQuery(STATS_CHAT, "PLAYERID", false) . 
							" GROUP BY " . STATS_CHAT . ".ROUNDID" . 
							" ORDER BY " . STATS_CHAT . ".ROUNDID DESC";

		// NO Order should be like said in the game
		$result = DB_Query($sqlquery);
		$content['ChatLog'] = DB_GetAllRows($result, true);
		
		if ( isset($content['ChatLog']) )
		{
			// Enable Player Stats
			$content['chatsfound'] = "true";

			for($i = 0; $i < count($content['ChatLog']); $i++)
			{
				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['ChatLog'][$i]['cssclass'] = "line0";
				else
					$content['ChatLog'][$i]['cssclass'] = "line1";
				// --- 

				// --- HTML Valid Text
				$content['ChatLog'][$i]['TextSaidAsHtml'] = GetPlayerNameAsHTML( $content['ChatLog'][$i]['TextSaid'] );
				// --- 
			}
			
			// Extend Player aliases
			FindAndFillTopAliases($content['ChatLog'], "PLAYERID", "Alias", "AliasAsHtml" );
		}
		else
		{
			$content['chatsfound'] = "false";
			$content['chatserror'] = GetAndReplaceLangStr($content["LN_SEARCH_CHATNOTFOUND"], $content['searchfor']);
		}
	}
	else
	{
		$content['chatsfound'] = "false";
		$content['chatserror'] = $content["LN_SEARCH_CHATTOSHORT"];
	}

}
else
	$content['searchfor'] = "";
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "find-chat.html");
$page -> output(); 
// --- 

?>
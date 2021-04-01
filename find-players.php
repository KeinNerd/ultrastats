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
	* ->	Search Players File
	*		Helper to search for players by name, id and pbguid
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
$content['TITLE'] .= " :: Search for Players ";
// --- END CREATE TITLE


// --- BEGIN Custom Code
if ( isset($_GET['search']) )
{
	if ( isset($_GET['searchtype']) )
	{
		// get and check, and may set default if needed
		$content['searchtype'] = intval(DB_RemoveBadChars($_GET['searchtype']));
		if ( $content['searchtype'] <= 0 || $content['searchtype'] > 3 )
			$content['searchtype'] = 2;
	}
	else	// Default = AliasSearch
		$content['searchtype'] = 2;

	if ( $content['searchtype'] == 1 ) { $content['searchtype_selected_1'] = "selected"; } else { $content['searchtype_selected_1'] = ""; } 
	if ( $content['searchtype'] == 2 ) { $content['searchtype_selected_2'] = "selected"; } else { $content['searchtype_selected_2'] = ""; } 
	if ( $content['searchtype'] == 3 ) { $content['searchtype_selected_3'] = "selected"; } else { $content['searchtype_selected_3'] = ""; } 

	// --- Set wherequery by Searchtype
	if (	$content['searchtype'] == 1 ) 
	{
		// Get as number
		$content['searchfor'] = intval(DB_RemoveBadChars($_GET['search']));
		
		// Set SQL Query
		$sqlquery = " WHERE " . STATS_ALIASES . ".PLAYERID = " . $content['searchfor']; 
	}
	else if($content['searchtype'] == 2 )
	{
		// Get normal
		$content['searchfor'] = DB_RemoveBadChars($_GET['search']);
		
		// Check for Ignore Color Codes
		if ( isset ($_GET['ignorecolorcodes']) ) { $content['IGNORECOLORCODES'] = true; } else {$content['IGNORECOLORCODES'] = 0; }
		if ( $content['IGNORECOLORCODES'] ) 
			$alias_wherefield = "AliasStrippedCodes"; 
		else
			$alias_wherefield = "Alias"; 

		// Set SQL Query
		$sqlquery = " WHERE " . STATS_ALIASES . "." . $alias_wherefield . " LIKE '%" . $content['searchfor'] . "%'";
	}
	else if($content['searchtype'] == 3 )
	{
		// Get normal
		$content['searchfor'] = DB_RemoveBadChars($_GET['search']);
		
		// Check for Ignore Color Codes
		$wherefield = "PBGUID"; 

		// Set SQL Query
		$sqlquery = " WHERE " . STATS_PLAYERS_STATIC . "." . $wherefield . " LIKE '%" . $content['searchfor'] . "%'";
	}
	// ---

	// --- Now get the players 
	$sqlquery = "SELECT " .
						STATS_ALIASES . ".PLAYERID, " . 
						STATS_ALIASES . ".Alias, " . 
						STATS_ALIASES . ".AliasAsHtml, " . 
						STATS_ALIASES . ".Count " . 
						" FROM " . STATS_ALIASES . 
						" INNER JOIN (" . STATS_PLAYERS_STATIC . 
						") ON (" . 
						STATS_PLAYERS_STATIC . ".GUID=" . STATS_ALIASES . ".PLAYERID) " . 
						$sqlquery . 
						GetCustomServerWhereQuery(STATS_ALIASES, false) . 
						GetBannedPlayerWhereQuery(STATS_ALIASES, "PLAYERID", false) . 
						" GROUP BY " . STATS_ALIASES . ".PLAYERID " . 
						" ORDER BY Count ";

	$result = DB_Query($sqlquery);
	$content['playersresults'] = DB_GetAllRows($result, true);
	if ( isset($content['playersresults']) )
	{
		// Enable Player Stats
		$content['playersfound'] = "true";

		for($i = 0; $i < count($content['playersresults']); $i++)
		{
			// --- Set Number
			$content['playersresults'][$i]['Number'] = $i+1;
			// ---

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['playersresults'][$i]['cssclass'] = "line1";
			else
				$content['playersresults'][$i]['cssclass'] = "line2";
			// --- 
		}
	}
	else
		$content['playersfound'] = "false";

}
else
	$content['searchfor'] = "";



if ( isset($content['IGNORECOLORCODES']) && $content['IGNORECOLORCODES'] )
	$content['IGNORECOLORCODES_CHECKED'] = "checked";
else
	$content['IGNORECOLORCODES_CHECKED'] = "";
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "find-players.html");
$page -> output(); 
// --- 

?>
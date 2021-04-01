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
	* ->	Map Info File
	*		Shows details and played rounds by map
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
$content['TITLE'] .= " :: Mapdetails ";
// --- END CREATE TITLE


// --- BEGIN Custom Code

// --- Get/Set Playersorting
if ( isset($_GET['id']) )
{
	// get and check
	$content['mapid'] = DB_RemoveBadChars($_GET['id']);
	
	// --- BEGIN LastRounds Code for front stats
	$sqlquery = "SELECT " .
						STATS_MAPS . ".MAPNAME, " . 
						STATS_MAPS . ".DisplayName, " . 
						STATS_MAPS . ".Description_id " . 
/*						STATS_LANGUAGE_STRINGS . ".TEXT as Description " . */
						" FROM " . STATS_MAPS . 
/*						" LEFT OUTER JOIN (" . STATS_LANGUAGE_STRINGS . 
						") ON (" . 
						STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_MAPS . ".Description_id) " . */
						" WHERE " . STATS_MAPS . ".MAPNAME  = '" . $content['mapid'] . "' " . 
						" LIMIT 1 ";
	$result = DB_Query($sqlquery);
	$mapvars = DB_GetSingleRow($result, true);
	if ( isset($mapvars['MAPNAME']) )
	{
		// Enable Stats
		$content['mapsenabled'] = "true";

		// --- Set Mapname 
		if ( strlen($mapvars['DisplayName']) > 0 )
			$content['MapDisplayName'] = $mapvars['DisplayName'];
		else
			$content['MapDisplayName'] = $mapvars['MAPNAME'];
		// --- 

		// Append to title
		$content['TITLE'] .= " for '" . $content['MapDisplayName'] . "'";

		// --- Set Mapimage
		$content['MapImage'] = $gl_root_path . "images/maps/middle/" . $mapvars['MAPNAME'] . ".jpg";
		if ( !is_file($content['MapImage']) )
			$content['MapImage'] = $gl_root_path . "images/maps/no-pic.png";
		// --- 

		// --- Copy other values
		$content['Description'] = GetTextFromDescriptionID( $mapvars['Description_id'], $content['LN_MAP_NODESCRIPTION'] );
		$content['MAPNAME'] = $mapvars['MAPNAME'];
		// --- 


		// --- Last Map Rounds 
		$sqlquery = "SELECT " .
							STATS_ROUNDS . ".ID, " .
							STATS_ROUNDS . ".TIMEADDED, " . 
							STATS_ROUNDS . ".ROUNDDURATION, " . 
							STATS_ROUNDS . ".AxisRoundWins, " . 
							STATS_ROUNDS . ".AlliesRoundWins, " .
							STATS_PLAYER_KILLS . ".PLAYERID, " . 
							STATS_GAMETYPES . ".NAME as GameTypeName, " . 
							STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName, " . 
							STATS_MAPS . ".MAPNAME ," . 
							STATS_MAPS . ".DisplayName as MapDisplayName" . 
							" FROM " . STATS_ROUNDS . 
							" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_PLAYER_KILLS .
							") ON (" . 
							STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
							STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID AND " . 
							STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID)" . 
							" WHERE " . STATS_MAPS . ".MAPNAME = '" . $content['mapid'] . "'" . 
							GetCustomServerWhereQuery( STATS_ROUNDS, false) . 
							" GROUP BY " . STATS_ROUNDS . ".ID" . 
							" ORDER BY TIMEADDED DESC LIMIT 20";
		$result = DB_Query($sqlquery);

		$content['lastrounds'] = DB_GetAllRows($result, true);
		if ( isset($content['lastrounds']) )
		{
			$content['lastroundsenable'] = "true";
			for($i = 0; $i < count($content['lastrounds']); $i++)
			{
				// --- Set Mapname 
				if ( strlen($content['lastrounds'][$i]['MapDisplayName']) > 0 )
					$content['lastrounds'][$i]['FinalMapDisplayName'] = $content['lastrounds'][$i]['MapDisplayName'];
				else
					$content['lastrounds'][$i]['FinalMapDisplayName'] = $content['lastrounds'][$i]['MAPNAME'];
				// --- 

				// --- Set Mapimage
				$content['lastrounds'][$i]['MapImage'] = $gl_root_path . "images/maps/thumbs/" . $content['lastrounds'][$i]['MAPNAME'] . ".jpg";
				if ( !is_file($content['lastrounds'][$i]['MapImage']) )
					$content['lastrounds'][$i]['MapImage'] = $gl_root_path . "images/maps/thumbs/no-pic.png";
				// --- 

				// --- Set GametypeName 
				if ( isset($content['lastrounds'][$i]['GameTypeDisplayName']) )
					$content['lastrounds'][$i]['FinalGameTypeDisplayName'] = $content['lastrounds'][$i]['GameTypeDisplayName'];
				else
					$content['lastrounds'][$i]['FinalGameTypeDisplayName'] = $content['lastrounds'][$i]['GameTypeName'];
				// --- 

				// --- Set Display Time
				$content['lastrounds'][$i]['TimePlayed'] = date('Y-m-d H:i:s', $content['lastrounds'][$i]['TIMEADDED']);
				// --- 

				// --- Set Display Time
				$content['lastrounds'][$i]['Number'] = $i+1;
				// --- 

				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['lastrounds'][$i]['cssclass'] = "line1";
				else
					$content['lastrounds'][$i]['cssclass'] = "line2";
				// --- 
			}
		}
		// --- 
	}
	else
		$content['iserror'] = "true";
}
else
{
	// Invalid ID!
	$content['iserror'] = "true";
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "info-maps.html");
$page -> output(); 
// --- 
?>
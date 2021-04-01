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
	* ->	Gametype Info File
	*		Shows player rounds and infos for gametypes
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
$content['TITLE'] .= " :: Gametypedetails ";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// --- Get/Set Playersorting
if ( isset($_GET['id']) )
{
	// get and check
	$content['gametypeid'] = DB_RemoveBadChars($_GET['id']);
	
	// --- BEGIN LastRounds Code for front stats
	$sqlquery = "SELECT " .
						STATS_GAMETYPES . ".NAME, " . 
						STATS_GAMETYPES . ".DisplayName, " . 
						STATS_LANGUAGE_STRINGS . ".TEXT as Description " .
						" FROM " . STATS_GAMETYPES . 
						" LEFT OUTER JOIN (" . STATS_LANGUAGE_STRINGS . 
						") ON (" . 
						STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_GAMETYPES . ".Description_id) " . 
						" WHERE " . STATS_GAMETYPES . ".NAME  = '" . $content['gametypeid'] . "' " . 
						" LIMIT 1 ";
	$result = DB_Query($sqlquery);
	$gametypevars = DB_GetSingleRow($result, true);
	if ( isset($gametypevars['NAME']) )
	{	
		// Enable Stats
		$content['gametypeenabled'] = "true";

		// --- Set Gametypename 
		if ( strlen($gametypevars['DisplayName']) > 0 )
			$content['GametypeDisplayName'] = $gametypevars['DisplayName'];
		else
			$content['GametypeDisplayName'] = $gametypevars['NAME'];
		// --- 
		
		// Append to title
		$content['TITLE'] .= " for '" . $content['GametypeDisplayName'] . "'";

		// --- Set Gametypeimage
		$content['GametypeImage'] = $gl_root_path . "images/gametypes/normal/" . $content['gen_gameversion_picpath'] . "/" . $gametypevars['NAME'] . ".png";
		if ( !is_file($content['GametypeImage']) )
			$content['GametypeImage'] = $gl_root_path . "images/gametypes/no-pic.png";
		// --- 

		// --- Copy other values
		if ( isset($gametypevars['Description']) && strlen($gametypevars['Description']) > 0 )
			$content['Description'] = $gametypevars['Description'];
		else
			$content['Description'] = $content['LN_GAMETYPE_NODESCRIPTION'];
		$content['NAME'] = $gametypevars['NAME'];
		// --- 


		// --- Last Map Rounds 
		$sqlquery = "SELECT " .
							STATS_ROUNDS . ".ID, " .
							STATS_ROUNDS . ".TIMEADDED, " . 
							STATS_ROUNDS . ".ROUNDDURATION, " . 
							STATS_ROUNDS . ".AxisRoundWins, " . 
							STATS_ROUNDS . ".AlliesRoundWins, " .
							STATS_PLAYER_KILLS . ".PLAYERID, " . 
//							STATS_GAMETYPES . ".NAME as GameTypeName, " . 
//							STATS_GAMETYPES . ".DisplayName as GameTypeDisplayName, " . 
							STATS_MAPS . ".MAPNAME ," . 
							STATS_MAPS . ".DisplayName as MapDisplayName" . 
							" FROM " . STATS_ROUNDS . 
							" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_PLAYER_KILLS .
							") ON (" . 
							STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
							STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID AND " . 
							STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID)" . 
							" WHERE " . STATS_GAMETYPES . ".NAME = '" . $content['gametypeid'] . "'" . 
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
$page -> parser($content, "info-gametypes.html");
$page -> output(); 
// --- 
?>
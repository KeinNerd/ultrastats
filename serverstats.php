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
	* ->	Server Stats File
	*		Shows played maps of a server 
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
$content['TITLE'] .= " :: Mapstats ";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// --- Read Vars
if ( isset($_GET['start']) )
	$content['current_pagebegin'] = intval(DB_RemoveBadChars($_GET['start']));
else
	$content['current_pagebegin'] = 0;
// --- 

// --- Only go ahead if Server is selected
if ( isset($content['serverid']) )
{
	// --- First get the Count and Set Pager Variables
	$sqlquery = "SELECT " .
						"count(" . STATS_MAPS . ".ID) as MapCount" .
						" FROM " . STATS_MAPS . 
						" INNER JOIN (" . STATS_ROUNDS . 
						") ON (" . 
						STATS_ROUNDS . ".MAPID =" . STATS_MAPS . ".ID " . 
						") " .
						GetCustomServerWhereQuery(STATS_ROUNDS, true) . 
						GetTimeWhereQueryStringForRoundTable() . 
						" GROUP BY " . STATS_MAPS . ".MAPNAME ";

	$content['maps_count'] = DB_GetRowCount( $sqlquery );
	if ( $content['maps_count'] > $content['web_maxmapsperpage'] ) 
	{
		$pagenumbers = $content['maps_count'] / $content['web_maxmapsperpage'];

		// Check PageBeginValue
		if ( $content['current_pagebegin'] > $content['maps_count'] )
			$content['current_pagebegin'] = 0;

		// Enable Player Pager
		$content['maps_pagerenabled'] = "true";
	}
	else
	{
		$content['current_pagebegin'] = 0;
		$pagenumbers = 0;
	}
	// --- 
	
	// --- BEGIN Get Played Maps Code for front stats
	$sqlquery = "SELECT " .
						STATS_MAPS . ".ID, " . 
						STATS_MAPS . ".MAPNAME, " . 
						STATS_MAPS . ".DisplayName, " . 
						"count(" . STATS_ROUNDS . ".MAPID) as MapCount" .
						" FROM " . STATS_MAPS . 
						" INNER JOIN (" . STATS_ROUNDS .
						") ON (" . 
						STATS_ROUNDS . ".MAPID =" . STATS_MAPS . ".ID " . 
						") " .
						GetCustomServerWhereQuery(STATS_ROUNDS, true) . 
						GetTimeWhereQueryStringForRoundTable() . 
						" GROUP BY " . STATS_MAPS . ".MAPNAME " .
						" ORDER BY MapCount DESC" .
						" LIMIT " . $content['current_pagebegin'] . " , " . $content['web_maxmapsperpage'];

	$result = DB_Query($sqlquery);
	$content['playedmaps'] = DB_GetAllRows($result, true);
	if ( isset($content['playedmaps']) )
	{
		// Enable
		$content['mapssenabled'] = "true";

		for($i = 0; $i < count($content['playedmaps']); $i++)
		{
			// --- Set Number
			$content['playedmaps'][$i]['Number'] = $i+1 + $content['current_pagebegin']; 
			// ---

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['playedmaps'][$i]['cssclass'] = "line1";
			else
				$content['playedmaps'][$i]['cssclass'] = "line2";
			// --- 

			// --- Set Mapname 
			if ( strlen($content['playedmaps'][$i]['DisplayName']) > 0 )
				$content['playedmaps'][$i]['MapDisplayName'] = $content['playedmaps'][$i]['DisplayName'];
			else
				$content['playedmaps'][$i]['MapDisplayName'] = $content['playedmaps'][$i]['MAPNAME'];
			// --- 

			// --- Set Mapimage
			$content['playedmaps'][$i]['MapImage'] = $gl_root_path . "images/maps/middle/" . $content['playedmaps'][$i]['MAPNAME'] . ".jpg";
			if ( !is_file($content['playedmaps'][$i]['MapImage']) )
				$content['playedmaps'][$i]['MapImage'] = $gl_root_path . "images/maps/no-pic.png";
			// --- 

			// --- Set Most Played Gametype
			$sqlquery = "SELECT " .
								STATS_GAMETYPES . ".NAME, " . 
								STATS_GAMETYPES . ".DisplayName, " . 
								"count(" . STATS_ROUNDS . ".GAMETYPE) as GametypeCount" .
								" FROM " . STATS_ROUNDS . 
								" INNER JOIN (" . STATS_GAMETYPES . 
								") ON (" . 
								STATS_GAMETYPES . ".ID =" . STATS_ROUNDS . ".GAMETYPE " . 
								") " .
								" WHERE " . STATS_ROUNDS . ".MAPID = " . $content['playedmaps'][$i]['ID'] . 
								GetCustomServerWhereQuery(STATS_ROUNDS, false) . 
								GetTimeWhereQueryStringForRoundTable() . 
								" GROUP BY " . STATS_ROUNDS . ".MAPID " .
								" ORDER BY GametypeCount DESC" .
								" LIMIT 1 ";
			$result = DB_Query($sqlquery);
			$gametypevars = DB_GetSingleRow( $result, true );
			if ( isset($gametypevars['GametypeCount']) )
			{
				$content['GameTypeCount'] = $gametypevars['GametypeCount'];
				$content['playedmaps'][$i]['GameTypeName'] = $gametypevars['NAME'];
				if ( strlen($gametypevars['DisplayName']) > 0 )
					$content['playedmaps'][$i]['GameTypeDisplayName'] = $gametypevars['DisplayName'];
				else
					$content['playedmaps'][$i]['GameTypeDisplayName'] = $gametypevars['NAME'];
			}
			else
			{
				$content['playedmaps'][$i]['GameTypeCount'] = "0";
				$content['playedmaps'][$i]['GameTypeName'] = "Unknown";
			}
			// --- 


			// --- Last Map Rounds 
			$sqlquery = "SELECT " .
								STATS_ROUNDS . ".ID as ROUNDID, " .
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
								" INNER JOIN (" . STATS_GAMETYPES . ", " . STATS_MAPS . ", " . STATS_PLAYER_KILLS . /*", " . STATS_TIME . */
								") ON (" . 
								STATS_GAMETYPES . ".ID=" . STATS_ROUNDS . ".GAMETYPE AND " . 
								STATS_MAPS . ".ID=" . STATS_ROUNDS . ".MAPID AND " . 
								STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID " . 
								/*" AND " . STATS_ROUNDS . ".ID=" . STATS_TIME . ".ROUNDID " . */
								") " . 
								" WHERE " . STATS_MAPS . ".MAPNAME = '" . $content['playedmaps'][$i]['MAPNAME'] . "'" . 
								GetCustomServerWhereQuery( STATS_ROUNDS, false) . 
								GetTimeWhereQueryStringForRoundTable() . 
								" GROUP BY " . STATS_ROUNDS . ".ID" . 
								" ORDER BY TIMEADDED DESC LIMIT 10";
			$result = DB_Query($sqlquery);
			$content['playedmaps'][$i]['lastrounds'] = DB_GetAllRows($result, true);
			if ( isset($content['playedmaps'][$i]['lastrounds']) )
			{
				$content['playedmaps'][$i]['lastroundsenable'] = "true";
				for($n = 0; $n < count($content['playedmaps'][$i]['lastrounds']); $n++)
				{
					// --- Set GametypeName 
					if ( isset($content['playedmaps'][$i]['lastrounds'][$n]['GameTypeDisplayName']) && strlen($content['playedmaps'][$i]['lastrounds'][$n]['GameTypeDisplayName']) > 0 )
						$content['playedmaps'][$i]['lastrounds'][$n]['FinalGameTypeDisplayName'] = $content['playedmaps'][$i]['lastrounds'][$n]['GameTypeDisplayName'];
					else
						$content['playedmaps'][$i]['lastrounds'][$n]['FinalGameTypeDisplayName'] = $content['playedmaps'][$i]['lastrounds'][$n]['GameTypeName'];
					// --- 

					// --- Set Display Time
					$content['playedmaps'][$i]['lastrounds'][$n]['TimePlayed'] = date('Y-m-d H:i:s', $content['playedmaps'][$i]['lastrounds'][$n]['TIMEADDED']);
					// --- 

					// --- Set Display Time
					$content['playedmaps'][$i]['lastrounds'][$n]['LRNumber'] = $n+1;
					// --- 

					// --- Set CSS Class
					if ( $n % 2 == 0 )
						$content['playedmaps'][$i]['lastrounds'][$n]['sub_cssclass'] = "line1";
					else
						$content['playedmaps'][$i]['lastrounds'][$n]['sub_cssclass'] = "line2";
					// --- 
				}
			}
			// --- 
		}

		// --- Now we create the Pager ;)!
			// Fix for now of the list exceeds $CFG['MAX_PAGES_COUNT'] pages
			if ($pagenumbers > $content['web_maxmapsperpage'])
			{
				$content['MAPS_MOREPAGES'] = "*(More then " . $content['web_maxmapsperpage'] . " pages found)";
				$pagenumbers = $content['web_maxmapsperpage'];
			}
			else
				$content['MAPS_MOREPAGES'] = "&nbsp;";

			for ($i=0 ; $i < $pagenumbers ; $i++)
			{
				$content['PLAYERPAGES'][$i]['mypagebegin'] = ($i * $content['web_maxmapsperpage']);

				if ($content['current_pagebegin'] == $content['PLAYERPAGES'][$i]['mypagebegin'])
					$content['PLAYERPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
				else
					$content['PLAYERPAGES'][$i]['mypagenumber'] = $i+1;

				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['PLAYERPAGES'][$i]['cssclass'] = "line1";
				else
					$content['PLAYERPAGES'][$i]['cssclass'] = "line2";
				// --- 
			}
		// ---

	}
	else
		$content['iserror'] = "true";
}
else
{
	// --- BEGIN ServerList Area
	$sqlquery = "SELECT " .
						"(" . STATS_SERVERS . ".ID) as ServerID, " . 
						"(" . STATS_SERVERS . ".NAME) as ServerName, " . 
						"(" . STATS_SERVERS . ".IP) as ServerIP, " . 
						"(" . STATS_SERVERS . ".Port) as ServerPort, " . 
						"(" . STATS_SERVERS . ".Description) as ServerDescription, " . 
						STATS_SERVERS . ".LastUpdate " . 
						" FROM " . STATS_SERVERS . 
						" ORDER BY ID ";
	$result = DB_Query( $sqlquery );

	$content['SERVERS'] = DB_GetAllRows($result, true);
	if ( isset($content['SERVERS']) )
	{
		// Serverlist in this case
		$content['serverlistenabled'] = "true";

		for($i = 0; $i < count($content['SERVERS']); $i++)
		{
			// Last Time
			$content['SERVERS'][$i]['LastUpdate_Formatted'] = date('Y-m-d H:i:s', $content['SERVERS'][$i]['LastUpdate']);

			// --- Set CSS Class
			if ( $i % 2 == 0 )
				$content['SERVERS'][$i]['cssclass'] = "line1";
			else
				$content['SERVERS'][$i]['cssclass'] = "line2";
			// --- 
		}
	}
	// --- END Main Info Area
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "serverstats.html");
$page -> output(); 
// --- 
?>
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
	* ->	Weapon Details File
	*		Shows Details for a weapon, or if no weapon is specified,
	*		a list of all available weapons!
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
$content['TITLE'] .= " :: Weapondetails ";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// --- Read Vars
if ( isset($_GET['mostkills_start']) )
	$content['current_mostkills_pagebegin'] = intval(DB_RemoveBadChars($_GET['mostkills_start']));
else
	$content['current_mostkills_pagebegin'] = 0;

if ( isset($_GET['killedby_start']) )
	$content['current_killedby_pagebegin'] = intval(DB_RemoveBadChars($_GET['killedby_start']));
else
	$content['current_killedby_pagebegin'] = 0;

// --- 

// --- Get/Set Weaponsorting
if ( isset($_GET['id']) )
{
	// get and check
	$content['weaponid'] = DB_RemoveBadChars($_GET['id']);
	
	// --- BEGIN Get Weapon Info's 
	$sqlquery = "SELECT " .
						STATS_WEAPONS . ".INGAMENAME, " . 
						STATS_WEAPONS . ".DisplayName, " . 
						STATS_WEAPONS . ".WeaponType, " . 
						STATS_WEAPONS . ".Description_id, " . 
						STATS_WEAPONS . ".ExternalInfoUrl " . 
/*						STATS_LANGUAGE_STRINGS . ".TEXT as Description " . */
						" FROM " . STATS_WEAPONS . 
/*						" LEFT OUTER JOIN (" . STATS_LANGUAGE_STRINGS . 
						") ON (" . 
						STATS_LANGUAGE_STRINGS . ".STRINGID =" . STATS_WEAPONS . ".Description_id) " . */
						" WHERE " . STATS_WEAPONS . ".INGAMENAME = '" . $content['weaponid'] . "' " . 
						" LIMIT 1 ";
	$result = DB_Query($sqlquery);
	$weaponvars = DB_GetSingleRow($result, true);
	if ( isset($weaponvars['INGAMENAME']) )
	{
		// Enable Stats
		$content['weaponsenabled'] = "true";

		// --- Set WeaponName 
		if ( strlen($weaponvars['DisplayName']) > 0 )
			$content['WeaponDisplayName'] = $weaponvars['DisplayName'];
		else
			$content['WeaponDisplayName'] = $weaponvars['INGAMENAME'];
		// --- 

		// --- Set WeaponTypeDisplayName 
		$content['WeaponTypeDisplayName'] = GetWeapontypeDisplayName( $weaponvars['WeaponType'] );
		// --- 

		// Append to Title
		$content['TITLE'] .= " for '" . $content['WeaponDisplayName'] . "'";

		// --- Set ExternalInfoUrl!
		if ( strlen($weaponvars['ExternalInfoUrl']) <= 0 )
		{
			$content['IsExternalInfoUrl'] = "false";
			$content['ExternalInfoUrl'] = "";
		}
		else
		{
			$content['ExternalInfoUrl'] = $weaponvars['ExternalInfoUrl'];
			$content['IsExternalInfoUrl'] = "true";
			$content['ExternalInfoUrlDisplay'] = strlen($content['ExternalInfoUrl']) > 45 ? substr($content['ExternalInfoUrl'], 0, 45) . "..." : $content['ExternalInfoUrl'];

		}
		// ---

		// --- Set Weaponimage
		// Do some replacements for same weapons ^^!
		$tmpWeaponimg = ReturnWeaponBaseName($weaponvars['INGAMENAME']);
		$content['WeaponImage'] = $gl_root_path . "images/weapons/normal/" . $tmpWeaponimg . ".png";
		if ( !is_file($content['WeaponImage']) )
			$content['WeaponImage'] = $gl_root_path . "images/weapons/no-pic.png";
		
		// Copy text!
		$content['WeaponImageDisplayName'] = $content['WeaponDisplayName']; 
		// --- 

		$content['Description'] = GetTextFromDescriptionID( $weaponvars['Description_id'], $content['LN_WEAPON_NODESCRIPTION'] );
		$content['INGAMENAME'] = $weaponvars['INGAMENAME'];
		// --- 
		
		// --- Scan for attachments and set them
		$content['AttachmentID'] = ObtainAttachmentNameFromWeapon($weaponvars['INGAMENAME']);
		if ( $content['AttachmentID'] != "none" ) 
		{
			// Enable Attachment
			$content['AttachmentEnabled'] = true;

			$content['AttachmentImage'] = $gl_root_path . "images/perks/normal/" . $content['AttachmentID'] . ".png";
			if ( !is_file($content['AttachmentImage']) )
				$content['AttachmentImage'] = $gl_root_path . "images/perks/no-pic.png";

			// Obtain DisplayName
			if ( strlen($content['attachments'][ $content['AttachmentID'] ]['DisplayName']) > 0 )
				$content['AttachmentDisplayName'] = $content['attachments'][$content['AttachmentID']]['DisplayName'];
			else
				$content['AttachmentDisplayName'] = $content['AttachmentID'];
		}
		// --- 

		// --- Most kills with this Weapon
			// --- First get the Count and Set Pager Variables
			$sqlquery = "SELECT " .
								"count(" . STATS_PLAYER_KILLS . ".PLAYERID) as AllPlayersCount, " . 
								"sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills " . 
								" FROM " . STATS_PLAYER_KILLS . 
								" INNER JOIN (" . STATS_WEAPONS . ", " . STATS_ROUNDS .
								") ON (" . 
								STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID AND " . 
								STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID " . 
								") " . 
								" WHERE " . STATS_WEAPONS . ".INGAMENAME = '" . $content['weaponid'] . "' " . 
								GetCustomServerWhereQuery(STATS_PLAYER_KILLS, false) . 
								GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
								GetTimeWhereQueryStringForRoundTable() . 
								" GROUP BY PLAYERID" . 
								" ORDER BY AllKills DESC ";
			$result = DB_Query($sqlquery);
			$content['mostkills_count'] = DB_GetRowCountByResult( $result );
			$tmpvars = DB_GetSingleRow($result, true);
			$content['mostskills_maxkills'] = $tmpvars['AllKills'];

			if ( $content['mostkills_count'] > $content['web_detaillistsplayers'] ) 
			{
				$mostkills_pagenumbers = $content['mostkills_count'] / $content['web_detaillistsplayers'];

				// Check PageBeginValue
				if ( $content['current_mostkills_pagebegin'] > $content['mostkills_count'] )
					$content['current_mostkills_pagebegin'] = 0;

				// Enable Player Pager
				$content['mostkills_pagerenabled'] = "true";
			}
			else
			{
				$content['current_mostkills_pagebegin'] = 0;
				$mostkills_pagenumbers = 0;
			}
			// --- 
		$sqlquery = "SELECT " .
							STATS_PLAYER_KILLS . ".PLAYERID, " . 
							"sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills " . 
							" FROM " . STATS_PLAYER_KILLS . 
							" INNER JOIN (" . STATS_WEAPONS . ", " . STATS_ROUNDS . 
							") ON (" . 
							STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID AND " . 
							STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID " . 
							") " .  
							" WHERE " . STATS_WEAPONS . ".INGAMENAME = '" . $content['weaponid'] . "' " . 
							GetCustomServerWhereQuery(STATS_PLAYER_KILLS, false) . 
							GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "PLAYERID", false) . 
							GetTimeWhereQueryStringForRoundTable() . 
							" GROUP BY PLAYERID" . 
							" ORDER BY AllKills DESC " . 
							" LIMIT " . $content['current_mostkills_pagebegin'] . " , " . $content['web_detaillistsplayers'];
		$result = DB_Query($sqlquery);
		$content['mostkills'] = DB_GetAllRows($result, true);
		if ( isset($content['mostkills']) )
		{
			$content['mostkillssenabled'] = "true";

			// Extend PlayerAliases
			FindAndFillTopAliases($content['mostkills'], "PLAYERID", "Alias", "AliasAsHtml" );

			// Set Max Percent for bars
			$maxpercent = $content['mostskills_maxkills']; // $content['mostkills'][0]['AllKills'];

			for($i = 0; $i < count($content['mostkills']); $i++)
			{
				// --- Set Number
				$content['mostkills'][$i]['Number'] = $i+1 + $content['current_mostkills_pagebegin'];
				// ---

				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['mostkills'][$i]['cssclass'] = "line1";
				else
					$content['mostkills'][$i]['cssclass'] = "line2";
				// --- 

				// --- Set Bar Image
				$content['mostkills'][$i]['KillBarPercent'] = intval(($content['mostkills'][$i]['AllKills'] / $maxpercent) * 100);
				$content['mostkills'][$i]['KillBarWidth'] = $content['mostkills'][$i]['KillBarPercent'] - 9; // Percentage Bar !

				$content['mostkills'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/blue_left_9.png";
				$content['mostkills'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/blue_middle_9.png";
				$content['mostkills'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/blue_right_9.png";
				// --- 
			}

			// --- Now we create the Pager ;)!
				// Fix for now of the list exceeds $CFG['MAX_PAGES_COUNT'] pages
				if ($mostkills_pagenumbers > $content['web_maxpages'])
				{
					$content['MOSTKILLS_MOREPAGES'] = "*(More then " . $content['web_maxpages'] . " pages found)";
					$mostkills_pagenumbers = $content['web_maxpages'];
				}
				else
					$content['MOSTKILLS_MOREPAGES'] = "&nbsp;";

				for ($i=0 ; $i < $mostkills_pagenumbers ; $i++)
				{
					$content['MOSTKILLSPAGES'][$i]['mypagebegin'] = ($i * $content['web_detaillistsplayers']);

					if ($content['current_mostkills_pagebegin'] == $content['MOSTKILLSPAGES'][$i]['mypagebegin'])
						$content['MOSTKILLSPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
					else
						$content['MOSTKILLSPAGES'][$i]['mypagenumber'] = $i+1;

					// --- Set CSS Class
					if ( $i % 2 == 0 )
						$content['MOSTKILLSPAGES'][$i]['cssclass'] = "line1";
					else
						$content['MOSTKILLSPAGES'][$i]['cssclass'] = "line2";
					// --- 
				}
			// ---
		}
		// --- 

		// --- Most Killed by this Weapon!
			// --- First get the Count and Set Pager Variables
			$sqlquery = "SELECT " .
								"count(" . STATS_PLAYER_KILLS . ".ENEMYID) as AllPlayersCount, " . 
								"sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills " . 
								" FROM " . STATS_PLAYER_KILLS . 
								" INNER JOIN (" . STATS_WEAPONS . ", " . STATS_ROUNDS . 
								") ON (" . 
								STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID AND " . 
								STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID " . 
								") " . 
								" WHERE " . STATS_WEAPONS . ".INGAMENAME = '" . $content['weaponid'] . "' " . 
								GetCustomServerWhereQuery(STATS_PLAYER_KILLS, false) . 
								GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "ENEMYID", false) . 
								GetTimeWhereQueryStringForRoundTable() . 
								" GROUP BY ENEMYID" . 
								" ORDER BY AllKills DESC ";
			$result = DB_Query($sqlquery);
			$content['killedby_count'] = DB_GetRowCountByResult( $result );
			$tmpvars = DB_GetSingleRow($result, true);
			$content['killedby_maxkills'] = $tmpvars['AllKills'];
			if ( $content['killedby_count'] > $content['web_detaillistsplayers'] ) 
			{
				$killedby_pagenumbers = $content['killedby_count'] / $content['web_detaillistsplayers'];

				// Check PageBeginValue
				if ( $content['current_killedby_pagebegin'] > $content['killedby_count'] )
					$content['current_killedby_pagebegin'] = 0;

				// Enable Player Pager
				$content['killedby_pagerenabled'] = "true";
			}
			else
			{
				$content['current_killedby_pagebegin'] = 0;
				$killedby_pagenumbers = 0;
			}
			// --- 
		$sqlquery = "SELECT " .
							STATS_PLAYER_KILLS . ".ENEMYID, " . 
							"sum(" . STATS_PLAYER_KILLS . ".Kills) as AllKills " . 
							" FROM " . STATS_PLAYER_KILLS . 
							" INNER JOIN (" . STATS_WEAPONS . ", " . STATS_ROUNDS . 
							") ON (" . 
							STATS_WEAPONS . ".ID=" . STATS_PLAYER_KILLS . ".WEAPONID AND " . 
							STATS_PLAYER_KILLS . ".ROUNDID=" . STATS_ROUNDS . ".ID " . 
							") " . 
							" WHERE " . STATS_WEAPONS . ".INGAMENAME = '" . $content['weaponid'] . "' " . 
							GetCustomServerWhereQuery(STATS_PLAYER_KILLS, false) . 
							GetBannedPlayerWhereQuery(STATS_PLAYER_KILLS, "ENEMYID", false) . 
							GetTimeWhereQueryStringForRoundTable() . 
							" GROUP BY ENEMYID" . 
							" ORDER BY AllKills DESC " . 
							" LIMIT " . $content['current_killedby_pagebegin'] . " , " . $content['web_detaillistsplayers'];
		$result = DB_Query($sqlquery);
		$content['mostkilledby'] = DB_GetAllRows($result, true);
		if ( isset($content['mostkilledby']) )
		{
			$content['mostkilledbyenabled'] = "true";

			// Extend PlayerAliases
			FindAndFillTopAliases($content['mostkilledby'], "ENEMYID", "Enemy", "EnemyAsHtml" );

			// Set Max Percent for bars
			$maxpercent = $content['killedby_maxkills']; // $content['mostkilledby'][0]['AllKills'];

			for($i = 0; $i < count($content['mostkilledby']); $i++)
			{
				// --- Set Number
				$content['mostkilledby'][$i]['Number'] = $i+1 + $content['current_killedby_pagebegin'];
				// ---

				// --- Set CSS Class
				if ( $i % 2 == 0 )
					$content['mostkilledby'][$i]['cssclass'] = "line1";
				else
					$content['mostkilledby'][$i]['cssclass'] = "line2";
				// --- 

				// --- Set Bar Image
				$content['mostkilledby'][$i]['KillBarPercent'] = intval(($content['mostkilledby'][$i]['AllKills'] / $maxpercent) * 100);
				$content['mostkilledby'][$i]['KillBarWidth'] = $content['mostkilledby'][$i]['KillBarPercent'] - 9; // Percentage Bar !

				$content['mostkilledby'][$i]['BarImageLeft'] = $gl_root_path . "images/bars/bar-small/blue_left_9.png";
				$content['mostkilledby'][$i]['BarImageMiddle'] = $gl_root_path . "images/bars/bar-small/blue_middle_9.png";
				$content['mostkilledby'][$i]['BarImageRight'] = $gl_root_path . "images/bars/bar-small/blue_right_9.png";
				// --- 
			}

			// --- Now we create the Pager ;)!
				// Fix for now of the list exceeds $CFG['MAX_PAGES_COUNT'] pages
				if ($killedby_pagenumbers > $content['web_maxpages'])
				{
					$content['KILLEDBY_MOREPAGES'] = "*(More then " . $content['web_maxpages'] . " pages found)";
					$killedby_pagenumbers = $content['web_maxpages'];
				}
				else
					$content['KILLEDBY_MOREPAGES'] = "&nbsp;";

				for ($i=0 ; $i < $killedby_pagenumbers ; $i++)
				{
					$content['KILLEDBYPAGES'][$i]['mypagebegin'] = ($i * $content['web_detaillistsplayers']);

					if ($content['current_killedby_pagebegin'] == $content['KILLEDBYPAGES'][$i]['mypagebegin'])
						$content['KILLEDBYPAGES'][$i]['mypagenumber'] = "<B>-> ".($i+1)." <-</B>";
					else
						$content['KILLEDBYPAGES'][$i]['mypagenumber'] = $i+1;

					// --- Set CSS Class
					if ( $i % 2 == 0 )
						$content['KILLEDBYPAGES'][$i]['cssclass'] = "line1";
					else
						$content['KILLEDBYPAGES'][$i]['cssclass'] = "line2";
					// --- 
				}
			// ---
		}
		// --- 
	}
	else
	{
		$content['iserror'] = "true";
		$content['ERROR_DETAILS'] = $content['LN_ERROR_INVALIDWEAPON'];
	}
}
else
{
	// No weapon ID means we list all weapons!

	// Append to Title
	$content['TITLE'] .= " - All weapons";

	// Now the real Query begins
	$sqlquery = "SELECT " .
						STATS_WEAPONS . ".ID as WEAPONID, " .
						STATS_WEAPONS . ".INGAMENAME, " . 
						STATS_WEAPONS . ".WeaponType, " . 
						STATS_WEAPONS . ".DisplayName as WeaponDisplayName, " . 
						STATS_WEAPONS . ".ExternalInfoUrl, " .
						" sum(" . STATS_WEAPONS_KILLS . ".PlayersCount) as PlayersCount, " . 
						" sum(" . STATS_WEAPONS_KILLS . ".Kills) as WeaponKills " . 

						" FROM " . STATS_WEAPONS . 
						" LEFT OUTER JOIN (" . STATS_WEAPONS_KILLS . ") " . 
						" ON (" . STATS_WEAPONS . ".ID=" . STATS_WEAPONS_KILLS . ".WEAPONID " . " )" . 
						" WHERE 1=1 " . /* dummy where appended */
						GetCustomServerWhereQuery(STATS_WEAPONS_KILLS, false) . 
						GetTimeWhereQueryString(STATS_WEAPONS_KILLS) . 
						" GROUP BY " . STATS_WEAPONS . ".ID " . 
						" ORDER BY DisplayName DESC ";

	$result = DB_Query($sqlquery);
	$content['weaponsonly'] = DB_GetAllRows($result, true);

	//print_r ( $content['weaponsonly'] );
	//exit;

	if ( isset($content['weaponsonly']) )
	{
		// Enable weapon list
		$content['weaponslist'] = "true";

		// Preprocess weapons first!
		$content['BarImageKillCount'] = $gl_root_path . "images/bars/bar-small/green_middle_9.png";
		$content['BarImagePlayerCount'] = $gl_root_path . "images/bars/bar-small/blue_middle_9.png";
		$content['AllPlayerCount'] = 0;

		for($i = 0; $i < count($content['weaponsonly']); $i++)
		{
			// Set MaxKillCount
			if ( !isset($content['MaxKillCount']) || $content['weaponsonly'][$i]['WeaponKills'] > $content['MaxKillCount'] )
				$content['MaxKillCount'] = $content['weaponsonly'][$i]['WeaponKills'];

			// Set MaxPlayerCount
			if ( !isset($content['MaxPlayerCount']) || $content['weaponsonly'][$i]['PlayersCount'] > $content['MaxPlayerCount'] )
				$content['MaxPlayerCount'] = $content['weaponsonly'][$i]['PlayersCount'];
		}

		// --- Loop through weapontypes
		foreach ( $content['weapontypes'] as $WeaponTypeNum => $myWeaponType ) 
		{
			// Copy basic properties
			$content['weaponcategories'][$WeaponTypeNum]["ID"] = $myWeaponType["ID"];
			$content['weaponcategories'][$WeaponTypeNum]["CategoryDisplayName"] = $myWeaponType["Name"];

			// --- Loop through weapons
			foreach ( $content['weaponsonly'] as $myWeapon ) 
			{
				// If same weapon type, append 
				if ( $myWeapon['WeaponType'] == $myWeaponType["ID"] ) 
				{
					// --- Set CSS Class
					if ( isset($content['weaponcategories'][$WeaponTypeNum]['myweapons']) ) 
						$iArrayIndex = count($content['weaponcategories'][$WeaponTypeNum]['myweapons']);
					else
						$iArrayIndex = 0;
					if ( $iArrayIndex % 2 == 0 )
						$myWeapon['cssclass'] = "line1";
					else
						$myWeapon['cssclass'] = "line2";
					// --- 

					// --- Set WeaponName 
					if ( strlen($myWeapon['WeaponDisplayName']) <= 0 )
						$myWeapon['WeaponDisplayName'] = $myWeapon['INGAMENAME'];
					// --- 

					// Init KillCount 
					if ( !isset($myWeapon['WeaponKills']) ) 
						$myWeapon['WeaponKills'] = 0;

					// --- Set Weaponimage
					// Do some replacements for same weapons ^^!
					$tmpWeaponimg = ReturnWeaponBaseName($myWeapon['INGAMENAME']);
					$myWeapon['WeaponImage'] = $gl_root_path . "images/weapons/thumbs/" . $tmpWeaponimg . ".png";
					if ( !is_file($myWeapon['WeaponImage']) )
						$myWeapon['WeaponImage'] = $gl_root_path . "images/weapons/thumbs/no-pic.png";
					// --- 

					// --- Set Popupdetails Text
					$myWeapon['KillCountText'] = GetAndReplaceLangStr( $content['LN_WEAPON_KILLCOUNT_TEXT'], $myWeapon['WeaponKills'] );
					$myWeapon['PlayerCountText'] = GetAndReplaceLangStr( $content['LN_WEAPON_PLAYERCOUNT_TEXT'], $myWeapon['PlayersCount'] );
					// ---

					// --- Scan for attachments and set them
					$myWeapon['AttachmentID'] = ObtainAttachmentNameFromWeapon($myWeapon['INGAMENAME']);

					$myWeapon['AttachmentImage'] = $gl_root_path . "images/perks/thumbs/" . $myWeapon['AttachmentID'] . ".png";
					if ( !is_file($myWeapon['AttachmentImage']) )
						$myWeapon['AttachmentImage'] = $gl_root_path . "images/perks/no-pic.png";
					
					// Obtain DisplayName for the attachment
					if ( strlen($content['attachments'][ $myWeapon['AttachmentID'] ]['DisplayName']) > 0 )
						$myWeapon['AttachmentDisplayName'] = $content['attachments'][$myWeapon['AttachmentID']]['DisplayName'];
					else
						$myWeapon['AttachmentDisplayName'] = $myWeapon['AttachmentID'];
					// --- 

					// --- Set ExternalInfoUrl!
					if ( strlen($myWeapon['ExternalInfoUrl']) <= 0 )
					{
						$myWeapon['IsExternalInfoUrl'] = "false";
						$myWeapon['ExternalInfoUrl'] = "";
					}
					else
					{
						$myWeapon['ExternalInfoUrl'] = $myWeapon['ExternalInfoUrl'];
						$myWeapon['IsExternalInfoUrl'] = "true";
						$myWeapon['ExternalInfoUrlDisplay'] = strlen($myWeapon['ExternalInfoUrl']) > 20 ? substr($myWeapon['ExternalInfoUrl'], 0, 20) . "..." : $myWeapon['ExternalInfoUrl'];

					}
					// ---

					// --- Generate weapon usage bars!

					// Set KillRatioWidth Bars
					if ( $myWeapon['WeaponKills'] > 0 )
						$myWeapon['KillRatioWidth'] = intval( $myWeapon['WeaponKills'] / ($content['MaxKillCount'] / 100) );
					else
						$myWeapon['KillRatioWidth'] = 1;

					// Set PlayerCountWidth Bars
					if ( $myWeapon['PlayersCount'] > 0 )
						$myWeapon['PlayerCountWidth'] = intval( $myWeapon['PlayersCount'] / ($content['MaxPlayerCount'] / 100) );
					else
						$myWeapon['PlayerCountWidth'] = 1;

					// ---

					// Finally append to array!
					$content['weaponcategories'][$WeaponTypeNum]['myweapons'][] = $myWeapon;
				}
			}
			
			// Enable List if weapons were found
			if ( isset($content['weaponcategories'][$WeaponTypeNum]['myweapons']) && count($content['weaponcategories'][$WeaponTypeNum]['myweapons']) > 0 )
				$content['weaponcategories'][$WeaponTypeNum]['weaponcategoriesenabled'] = "true";
		}
	}
	else
	{
		$content['iserror'] = "true";
		$content['ERROR_DETAILS'] = $content['LN_ERROR_NOSTATSDATAFOUND'];
	}
}
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "weapons.html");
$page -> output(); 
// --- 
?>
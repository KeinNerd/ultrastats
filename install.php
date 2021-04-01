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
	* ->	Installer File													
	*		This file will help and guide you to install UltraStats!
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
define('IN_ULTRASTATS_INSTALL', true);		// Extra for INSTALL Script!
define('STEPSCRIPTNAME', "install.php");	// Helper variable for the STEP helper functions
$gl_root_path = './';

// Necessary include files
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/functions_frontendhelpers.php');
//include($gl_root_path . 'include/functions_installhelpers.php');

// Init Langauge first!
IncludeLanguageFile( $gl_root_path . '/lang/' . $LANG . '/main.php' );

InitBasicUltraStats();
if ( InitUltraStatsConfigFile(false) ) 
	DieWithErrorMsg( $content['LN_INSTALL_ERRORINSTALLED'] );

// Set some static values
define('MAX_STEPS', 7);
$content['web_theme'] = "codww";
$content['user_theme'] = "codww";
$configsamplefile = $content['BASEPATH'] . "contrib/config.sample.php"; 
//ini_set('error_reporting', E_ERROR); // NO PHP ERROR'S!
// ***					*** //

// --- CONTENT Vars
$content['TITLE'] = "Ultrastats :: " . $content['LN_INSTALL_TITLE'];
// --- 

// --- Read Vars
if ( isset($_GET['step']) )
{
	$content['INSTALL_STEP'] = intval(DB_RemoveBadChars($_GET['step']));
	if ( $content['INSTALL_STEP'] > MAX_STEPS ) 
		$content['INSTALL_STEP'] = 1;
}
else
	$content['INSTALL_STEP'] = 1;

// Set Next Step 
$content['INSTALL_NEXT_STEP'] = intval($content['INSTALL_STEP']);

if ( MAX_STEPS > $content['INSTALL_STEP'] )
{
	$content['NEXT_ENABLED'] = "true";
	$content['FINISH_ENABLED'] = "false";
	$content['INSTALL_NEXT_STEP']++;
}
else
{
	$content['NEXT_ENABLED'] = "false";
	$content['FINISH_ENABLED'] = "true";
}
// --- 


// --- BEGIN Custom Code
// --- Set Bar Image
	$content['BarImagePlus'] = $gl_root_path . "images/bars/bar-middle/green_middle_17.png";
	$content['BarImageLeft'] = $gl_root_path . "images/bars/bar-middle/green_left_17.png";
	$content['BarImageRight'] = $gl_root_path . "images/bars/bar-middle/green_right_17.png";
	$content['WidthPlus'] = intval( $content['INSTALL_STEP'] * (100 / MAX_STEPS) ) - 8;
	$content['WidthPlusText'] = "Installer Step " . $content['INSTALL_STEP'];
// --- 

// --- Set Title
$content['TITLE'] = GetAndReplaceLangStr( $content['TITLE'], $content['INSTALL_STEP'] );
$content['LN_INSTALL_TITLETOP'] = GetAndReplaceLangStr( $content['LN_INSTALL_TITLETOP'], $content['BUILDNUMBER'],  $content['INSTALL_STEP'] );
// --- 


// --- Start Setup Processing
if ( $content['INSTALL_STEP'] == 2 )
{	
	// Check if file permissions are correctly
	$content['fileperm'][0]['FILE_NAME'] = $content['BASEPATH'] . "config.php"; 
	$content['fileperm'][0]['FILE_TYPE'] = "file"; 
	$content['fileperm'][1]['FILE_NAME'] = $content['BASEPATH'] . "gamelogs/"; 
	$content['fileperm'][1]['FILE_TYPE'] = "dir"; 
	$content['fileperm'][2]['FILE_NAME'] = $content['BASEPATH'] . "images/maps/"; 
	$content['fileperm'][2]['FILE_TYPE'] = "dir"; 
	$content['fileperm'][3]['FILE_NAME'] = $content['BASEPATH'] . "images/serverlogos/"; 
	$content['fileperm'][3]['FILE_TYPE'] = "dir"; 
	$content['fileperm'][4]['FILE_NAME'] = $content['BASEPATH'] . "images/weapons/"; 
	$content['fileperm'][4]['FILE_TYPE'] = "dir"; 

//	Check file by file
	$bSuccess = true;
	for($i = 0; $i < count($content['fileperm']); $i++)
	{
		// --- Set CSS Class
		if ( $i % 2 == 0 )
			$content['fileperm'][$i]['cssclass'] = "line1";
		else
			$content['fileperm'][$i]['cssclass'] = "line2";
		// --- 

		if ( $content['fileperm'][$i]['FILE_TYPE'] == "dir" ) 
		{
			// Get Permission mask
			$perms = fileperms( $content['fileperm'][$i]['FILE_NAME'] );

			// World
			$iswriteable = (($perms & 0x0004) ? true : false) && (($perms & 0x0002) ? true : false);
			if ( $iswriteable ) 
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#007700";
				$content['fileperm'][$i]['ISSUCCESS'] = "Writeable"; 
			}
			else
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#770000";
				$content['fileperm'][$i]['ISSUCCESS'] = "NOT Writeable"; 
				$bSuccess = false;
			}
		}
		else
		{
			if ( !is_writable($content['fileperm'][$i]['FILE_NAME']) ) 
			{
				// Try to create an empty file
				$handle = @fopen( $content['fileperm'][$i]['FILE_NAME'] , "x");
				if ( $handle ) 
					fclose($handle);
			}

			if ( is_writable($content['fileperm'][$i]['FILE_NAME']) ) 
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#007700";
				$content['fileperm'][$i]['ISSUCCESS'] = "Writeable"; 
			}
			else
			{
				$content['fileperm'][$i]['BGCOLOR'] = "#770000";
				$content['fileperm'][$i]['ISSUCCESS'] = "NOT Writeable"; 
				$bSuccess = false;
			}
		}
	}

	if ( !$bSuccess )
	{
		$content['NEXT_ENABLED'] = "false";
		$content['RECHECK_ENABLED'] = "true";
		$content['iserror'] = "true";
		$content['errormsg'] = GetAndReplaceLangStr( $content['LN_INSTALL_FILEORDIRNOTWRITEABLE'], "touch config.php", "chmod 666 ./config.php", "chmod 777 ./gamelogs/ ./images/maps/ ./images/serverlogos/ ./images/weapons/");
	}

	// Check if sample config file is available
	if ( !is_file($configsamplefile) || GetFileLength($configsamplefile) <= 0 )
	{
		$content['NEXT_ENABLED'] = "false";
		$content['RECHECK_ENABLED'] = "true";
		$content['iserror'] = "true";
		$content['errormsg'] = GetAndReplaceLangStr( $content['LN_INSTALL_SAMPLECONFIGMISSING'], $configsamplefile);
	}

}
else if ( $content['INSTALL_STEP'] == 3 )
{	
	//Preinit vars
	if ( isset($_SESSION['DB_HOST']) ) { $content['DB_HOST'] = $_SESSION['DB_HOST']; } else { $content['DB_HOST'] = "localhost"; }
	if ( isset($_SESSION['DB_PORT']) ) { $content['DB_PORT'] = $_SESSION['DB_PORT']; } else { $content['DB_PORT'] = "3306"; }
	if ( isset($_SESSION['DB_NAME']) ) { $content['DB_NAME'] = $_SESSION['DB_NAME']; } else { $content['DB_NAME'] = "ultrastats"; }
	if ( isset($_SESSION['DB_PREFIX']) ) { $content['DB_PREFIX'] = $_SESSION['DB_PREFIX']; } else { $content['DB_PREFIX'] = "stats_"; }
	if ( isset($_SESSION['DB_USER']) ) { $content['DB_USER'] = $_SESSION['DB_USER']; } else { $content['DB_USER'] = "user"; }
	if ( isset($_SESSION['DB_PASS']) ) { $content['DB_PASS'] = $_SESSION['DB_PASS']; } else { $content['DB_PASS'] = ""; }

	// Check for Error Msg
	if ( isset($_GET['errormsg']) )
	{
		$content['iserror'] = "true";
		$content['errormsg'] = DB_RemoveBadChars( urldecode($_GET['errormsg']) );
	}

	// Create Gameversions List and set default game version
	$content['gen_gameversion'] = CODWW;
	CreateGameVersionsList();
}
else if ( $content['INSTALL_STEP'] == 4 )
{	
	// Read vars
	if ( isset($_POST['db_host']) )
		$_SESSION['DB_HOST'] = DB_RemoveBadChars($_POST['db_host']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_CFG_PARAMMISSING'] . $content['LN_CFG_DBSERVER'] );

	if ( isset($_POST['db_port']) )
		$_SESSION['DB_PORT'] = intval(DB_RemoveBadChars($_POST['db_port']));
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_CFG_PARAMMISSING'] . $content['LN_CFG_DBPORT'] );

	if ( isset($_POST['db_name']) )
		$_SESSION['DB_NAME'] = DB_RemoveBadChars($_POST['db_name']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_CFG_PARAMMISSING'] . $content['LN_CFG_DBNAME'] );

	if ( isset($_POST['db_prefix']) )
		$_SESSION['DB_PREFIX'] = DB_RemoveBadChars($_POST['db_prefix']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_CFG_PARAMMISSING'] . $content['LN_CFG_DBPREF'] );

	if ( isset($_POST['db_user']) )
		$_SESSION['DB_USER'] = DB_RemoveBadChars($_POST['db_user']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_CFG_PARAMMISSING'] . $content['LN_CFG_DBUSER'] );

	if ( isset($_POST['db_pass']) )
		$_SESSION['DB_PASS'] = DB_RemoveBadChars($_POST['db_pass']);
	else
		$_SESSION['DB_PASS'] = "";

	if ( isset($_POST['gen_gameversion']) )
		$_SESSION['GEN_GAMEVER'] = intval(DB_RemoveBadChars($_POST['gen_gameversion']));
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_CFG_PARAMMISSING'] . $content['LN_CFG_GAMEVER'] );

	// Now Check database connect
	$link_id = mysql_connect( $_SESSION['DB_HOST'], $_SESSION['DB_USER'], $_SESSION['DB_PASS']);
	if (!$link_id) 
		RevertOneStep( $content['INSTALL_STEP']-1, GetAndReplaceLangStr( $content['LN_INSTALL_ERRORCONNECTFAILED'], $_SESSION['DB_HOST']) . "<br>" . DB_ReturnSimpleErrorMsg() );
	
	// Try to select the DB!
	$db_selected = mysql_select_db($_SESSION['DB_NAME'], $link_id);
	if(!$db_selected) 
		RevertOneStep( $content['INSTALL_STEP']-1, GetAndReplaceLangStr( $content['LN_INSTALL_ERRORACCESSDENIED'], $_SESSION['DB_NAME']) . "<br>" . DB_ReturnSimpleErrorMsg() );

	// Looks good, now we write the config.php file!
//	ini_set('error_reporting', E_WARNING); // Enable Warnings!
}
else if ( $content['INSTALL_STEP'] == 5 )
{
	// Init sql variables
	$content['sql_sucess'] = 0;
	$content['sql_failed'] = 0;

	// Init $totaldbdefs
	$totaldbdefs = "";

	// Read the table GLOBAL definitions 
	ImportDataFile( $content['BASEPATH'] . "contrib/db_template.txt" );

	// Append Gamespecific definitions ^^
	if ( $_SESSION['GEN_GAMEVER'] == COD || $_SESSION['GEN_GAMEVER'] == CODUO || $_SESSION['GEN_GAMEVER'] == COD2 )
		ImportDataFile( $content['BASEPATH'] . "contrib/db_template_codww2only.txt" );
	else if ( $_SESSION['GEN_GAMEVER'] == COD4 )
		ImportDataFile( $content['BASEPATH'] . "contrib/db_template_cod4only.txt" );
	else if ( $_SESSION['GEN_GAMEVER'] == CODWW )
		ImportDataFile( $content['BASEPATH'] . "contrib/db_template_codwwonly.txt" );

	// Continue if no error occured while loading the db files
	if (!isset($content['iserror']) || $content['iserror'] == "false" ) 
	{
		// Process definitions ^^
		if ( strlen($totaldbdefs) <= 0 )
		{
			$content['failedstatements'][ $content['sql_failed'] ]['myerrmsg'] = GetAndReplaceLangStr( $content['LN_INSTALL_ERRORINVALIDDBFILE'], $content['BASEPATH'] . "include/db_template.txt");
			$content['failedstatements'][ $content['sql_failed'] ]['mystatement'] = "";
			$content['sql_failed']++;
		}

		// Replace stats_ with the custom one ;)
		$totaldbdefs = str_replace( "`stats_", "`" . $_SESSION["DB_PREFIX"], $totaldbdefs );
		
		// Now split by sql command
		$mycommands = split( ";\r\n", $totaldbdefs );
		
		// check for different linefeed
		if ( count($mycommands) <= 1 )
			$mycommands = split( ";\n", $totaldbdefs );

		//Still only one? Abort
		if ( count($mycommands) <= 1 )
		{
			$content['failedstatements'][ $content['sql_failed'] ]['myerrmsg'] = GetAndReplaceLangStr( $content['LN_INSTALL_ERRORINSQLCOMMANDS'], $content['BASEPATH'] . "include/db_template.txt"); 
			$content['failedstatements'][ $content['sql_failed'] ]['mystatement'] = "";
			$content['sql_failed']++;
		}

		// Append INSERT Statement for Config Table to set the GameVersion and Database Version ^^!
		$mycommands[count($mycommands)] = "INSERT INTO `" . $_SESSION["DB_PREFIX"] . "config` (`name`, `value`) VALUES ('gen_gameversion', '" . $_SESSION['GEN_GAMEVER'] . "')";
		$mycommands[count($mycommands)] = "INSERT INTO `" . $_SESSION["DB_PREFIX"] . "config` (`name`, `value`) VALUES ('database_installedversion', " . $content['database_internalversion'] . ")";

		// --- Now execute all commands
		@ini_set('error_reporting', E_WARNING); // Enable Warnings!
		InitUserDbSettings();
	//	InitUltraStatsConfigFile();

		// Establish DB Connection
		DB_Connect();

		for($i = 0; $i < count($mycommands); $i++)
		{
			if ( strlen(trim($mycommands[$i])) > 1 )
			{
				$result = DB_Query( $mycommands[$i], false );
				if ($result == FALSE)
				{
					$content['failedstatements'][ $content['sql_failed'] ]['myerrmsg'] = DB_ReturnSimpleErrorMsg();
					$content['failedstatements'][ $content['sql_failed'] ]['mystatement'] = $mycommands[$i];

					// --- Set CSS Class
					if ( $content['sql_failed'] % 2 == 0 )
						$content['failedstatements'][ $content['sql_failed'] ]['cssclass'] = "line1";
					else
						$content['failedstatements'][ $content['sql_failed'] ]['cssclass'] = "line2";
					// --- 

					$content['sql_failed']++;
				}
				else
					$content['sql_sucess']++;

				// Free result
				DB_FreeQuery($result);
			}
		}
		
		// Show results
		$content['showsqlresults'] = true;
	}
	else
		// do NOT Show results
		$content['showsqlresults'] = false;
}
else if ( $content['INSTALL_STEP'] == 6 )
{
	if ( isset($_SESSION['MAIN_Username']) )
		$content['MAIN_Username'] = $_SESSION['MAIN_Username'];
	else
		$content['MAIN_Username'] = "";

	$content['MAIN_Password1'] = "";
	$content['MAIN_Password2'] = "";

	// Check for Error Msg
	if ( isset($_GET['errormsg']) )
	{
		$content['iserror'] = "true";
		$content['errormsg'] = urldecode($_GET['errormsg']);
	}
}
else if ( $content['INSTALL_STEP'] == 7 )
{
	// --- 
	if ( isset($_POST['username']) )
		$_SESSION['MAIN_Username'] = DB_RemoveBadChars($_POST['username']);
	else
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_INSTALL_MISSINGUSERNAME'] );

	if ( isset($_POST['password1']) )
		$_SESSION['MAIN_Password1'] = DB_RemoveBadChars($_POST['password1']);
	else
		$_SESSION['MAIN_Password1'] = "";

	if ( isset($_POST['password2']) )
		$_SESSION['MAIN_Password2'] = DB_RemoveBadChars($_POST['password2']);
	else
		$_SESSION['MAIN_Password2'] = "";

	if (	
			strlen($_SESSION['MAIN_Password1']) <= 4 ||
			$_SESSION['MAIN_Password1'] != $_SESSION['MAIN_Password2'] 
		)
		RevertOneStep( $content['INSTALL_STEP']-1, $content['LN_INSTALL_PASSWORDNOTMATCH'] );
	// --- 


	// --- Create User Account
//	ini_set('error_reporting', E_WARNING); // Enable Warnings!
	InitUserDbSettings();		// We need some DB Settings
//	InitUltraStatsConfigFile();

	// Establish DB Connection
	DB_Connect();

	// Everything is fine, lets go create the User!
	CreateUserName( $_SESSION['MAIN_Username'], $_SESSION['MAIN_Password1'], 0 );

	// Show User success!
	$content['MAIN_Username'] = $_SESSION['MAIN_Username'];
	$content['createduser'] = true;
	// --- 

	// --- Create CONFIG FILE NOW at the last step!
	// If we reached this point, we have gathered all necessary information to create our configuration file ;)!
	$filebuffer = LoadDataFile($configsamplefile);

	$patterns[] = "/\\\$CFG\['DBServer'\] = (.*?);/";
	$patterns[] = "/\\\$CFG\['Port'\] = (.*?);/";
	$patterns[] = "/\\\$CFG\['DBName'\] = (.*?);/";
	$patterns[] = "/\\\$CFG\['TBPref'\] = (.*?);/";
	$patterns[] = "/\\\$CFG\['User'\] = (.*?);/";
	$patterns[] = "/\\\$CFG\['Pass'\] = (.*?);/";

	$replacements[] = "\$CFG['DBServer'] = '" . $_SESSION['DB_HOST'] . "';";
	$replacements[] = "\$CFG['Port'] = " . $_SESSION['DB_PORT'] . ";";
	$replacements[] = "\$CFG['DBName'] = '" . $_SESSION['DB_NAME'] . "';";
	$replacements[] = "\$CFG['TBPref'] = '" . $_SESSION['DB_PREFIX'] . "';";
	$replacements[] = "\$CFG['User'] = '" . $_SESSION['DB_USER'] . "';";
	$replacements[] = "\$CFG['Pass'] = '" . $_SESSION['DB_PASS'] . "';";

	// One call to replace them all ^^
	$filebuffer = preg_replace( $patterns, $replacements, $filebuffer );

	// --- Create file and write config into it!
	$handle = @fopen( $content['BASEPATH'] . "config.php" , "w");
	if ( $handle === false ) 
		RevertOneStep( $content['INSTALL_STEP']-1, GetAndReplaceLangStr($content['LN_INSTALL_FAILEDCREATECFGFILE'], $content['BASEPATH'] . "config.php") );
	
	fwrite($handle, $filebuffer);
	fclose($handle);
	// --- 
}
// --- 



// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "install.html");
$page -> output(); 
// ---

// --- Helper functions
function InitUserDbSettings()
{
	global $CFG;

	// Init DB Configs 
	$CFG['DBServer'] = $_SESSION['DB_HOST'];
	$CFG['Port'] = $_SESSION['DB_PORT'];
	$CFG['DBName'] = $_SESSION['DB_NAME'];
	$CFG['TBPref'] = $_SESSION['DB_PREFIX'];
	$CFG['User'] = $_SESSION['DB_USER'];
	$CFG['Pass'] = $_SESSION['DB_PASS'];
	
	// Needed table defs
	define('STATS_CONFIG',			$CFG['TBPref'] . "config");
	define('STATS_USERS',			$CFG['TBPref'] . "users");

}

function LoadDataFile($szFileName)
{
	global $content;

	// Lets read the table definitions :)
	$buffer = "";
	$handle = @fopen($szFileName, "r");
	if ($handle === false) 
		RevertOneStep( $content['INSTALL_STEP']-1, GetAndReplaceLangStr($content['LN_INSTALL_FAILEDREADINGFILE'], $szFileName) );
	else
	{
		while (!feof($handle)) 
		{
			$buffer .= fgets($handle, 4096);
		}
	   fclose($handle);
	}

	// return file buffer!
	return $buffer;
}
function RevertOneStep($stepback, $errormsg)
{
	header("Location: install.php?step=" . $stepback . "&errormsg=" . urlencode($errormsg) );
	exit;
}

function ImportDataFile($szFileName)
{
	global $content, $totaldbdefs;

	// Lets read the table definitions :)
	$handle = @fopen($szFileName, "r");
	if ($handle === false) 
	{
		$content['NEXT_ENABLED'] = "false";
		$content['RECHECK_ENABLED'] = "true";
		$content['iserror'] = "true";
		$content['errormsg'] = GetAndReplaceLangStr( $content['LN_INSTALL_MISSINGDBFILE'], $szFileName);
	}
	else
	{
		while (!feof($handle)) 
		{
			$buffer = fgets($handle, 4096);

			$pos = strpos($buffer, "--");
			if ($pos === false)
				$totaldbdefs .= $buffer; 
			else if ( $pos > 2 && strlen( trim($buffer) ) > 1 )
				$totaldbdefs .= $buffer; 
		}
	   fclose($handle);
	}
}

// ---
?>
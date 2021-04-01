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
	* ->	UserChange File
	*		Helper script to change user options and redirect to the 
	*		previous page. 
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

// --- BEGIN Custom Code
if ( isset($_SERVER['HTTP_REFERER']) )
	$szRedir = $_SERVER['HTTP_REFERER']; 
else
	$szRedir = "index.php"; // Default


if ( isset($_GET['op']) )
{
	if		( $_GET['op'] == "changestyle" ) 
	{
		if ( VerifyTheme($_GET['stylename']) ) 
			$_SESSION['CUSTOM_THEME'] = $_GET['stylename'];
	}
	else if ( $_GET['op'] == "changelang" ) 
	{
		if ( VerifyLanguage($_GET['langcode']) ) 
			$_SESSION['CUSTOM_LANG'] = $_GET['langcode'];
	}
	else if ( $_GET['op'] == "changeyear" ) 
	{
		if ( $content['ENABLETIMEFILTER'] ) 
		{
			if ( $_GET['newyear'] == "ALL_YEARS" ) 
			{
				unset( $_SESSION['TIME_SELECTEDYEAR'] );
				unset( $_SESSION['TIME_SELECTEDMONTH'] );
			}
			else if ( isset($content['TIMEYEARS'][ $_GET['newyear'] ]) ) 
			{
				$_SESSION['TIME_SELECTEDYEAR'] = $_GET['newyear'];

				// Unset current month selection!
				unset( $_SESSION['TIME_SELECTEDMONTH'] );
			}
		}
	}
	else if ( $_GET['op'] == "changemonth" ) 
	{
		if ( $content['ENABLETIMEFILTER'] ) 
		{
			if ( $_GET['newmonth'] == "ALL_MONTHS" ) 
				unset($_SESSION['TIME_SELECTEDMONTH']);
			else if ( isset($_SESSION['TIME_SELECTEDYEAR']) && isset($content['TIMEMONTHS'][ $_GET['newmonth'] ]) ) 
				$_SESSION['TIME_SELECTEDMONTH'] = $_GET['newmonth'];
		}
	}
}

// Final redirect
RedirectPage( $szRedir );
// --- 
?>
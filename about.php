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
	* ->	About File
	*		Shows everything about UltraStats you need to know. 
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
$content['TITLE'] .= " :: About UltraStats ";
// --- END CREATE TITLE


// --- BEGIN Custom Code
// NONE 
// --- 

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "about.html");
$page -> output(); 
// --- 
?>
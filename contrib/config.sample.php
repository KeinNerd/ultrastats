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
	* ->	Main Configuration File													
	*		Configuration need variables for the Database connection!
	*																	
	* This file is part of UltraStats
	*
	* UltraStats is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published
	* by the Free Software Foundation, either version 3 of the License,
	* or (at your option) any later version.
	********************************************************************
*/

// --- Database options
$CFG['DBServer'] = "localhost";
$CFG['Port'] = 3306;
$CFG['DBName'] = ""; 
$CFG['TBPref'] = "stats_"; 
$CFG['User'] = "root";
$CFG['Pass'] = "";
// --- 

// --- Other Configfile only Options 
$CFG["ShowPageRenderStats"] = 1;						// If enabled, you will see Pagerender Settings
$CFG["ShowDebugMsg"] = 0;								// Print additional debug informations!					
// --- 

?>
/* Detect Browser Version */
var szBrowserApp = "MOZILLA"; // Default!
if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))
{
	if (!/Opera[\/\s](\d+\.\d+)/.test(navigator.userAgent)) 
	{
		// Set browser to Internet Explorer
		szBrowserApp = "IEXPLORER";
	}
}

/*
*
*	Helper Javascript functions
*/
function CheckAlphaPNGImage(ImageName, ImageTrans)
{
	var agt=navigator.userAgent.toLowerCase();
	var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));

	if (is_ie)
		document.images[ImageName].src = ImageTrans;
}

function NewWindow(Location, WindowName,X_width,Y_height,Option) {
	var windowReference;
	var Addressbar = "location=NO";		//Default
	var OptAddressBar = "AddressBar";	//Default f�r Adressbar
	if (Option == OptAddressBar) {		//Falls AdressBar gew�nscht wird
		Addressbar = "location=YES";
		}
	windowReference = window.open(Location,WindowName, 
	'toolbar=no,' + Addressbar + ',directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=' + X_width + 
	',height=' + Y_height);
	if (!windowReference.opener)
		windowReference.opener = self;

}

// helper array to keep track of the timeouts!
var runningTimeouts = new Array();
var defaultMenuTimeout = 3000;
/*
* Toggle display type from NONE to BLOCK
*/ 
function ToggleDisplayTypeById(ObjID)
{
	var obj = document.getElementById(ObjID);
	if (obj != null)
	{
		if (obj.style.display == '' || obj.style.display == 'none')
		{
			obj.style.display='block';
			
			// Set Timeout to make sure the menu disappears
			ToggleDisplaySetTimeout(ObjID);
		}
		else
		{
			obj.style.display='none';
			
			// Abort Timeout if set!
			ToggleDisplayClearTimeout(ObjID);
		}
	}
}

function ToggleDisplaySetTimeout(ObjID)
{
	// Set Timeout 
	var szTimeOut = "ToggleDisplayOffTypeById('" + ObjID + "')";
	runningTimeouts[ObjID] = window.setTimeout(szTimeOut, defaultMenuTimeout);
}

function ToggleDisplayClearTimeout(ObjID)
{
	// Abort Timeout if set!
	if ( runningTimeouts[ObjID] != null )
	{
		window.clearTimeout(runningTimeouts[ObjID]);
	}
}

function ToggleDisplayEnhanceTimeOut(ObjID)
{
	// Only perform if timeout exists!
	if (runningTimeouts[ObjID] != null)
	{
		// First clear timeout
		ToggleDisplayClearTimeout(ObjID);

		// Set new  timeout
		ToggleDisplaySetTimeout(ObjID);
	}
}

/*
* Make Style sheet display OFF in any case
*/ 
function ToggleDisplayOffTypeById(ObjID)
{
	var obj = document.getElementById(ObjID);
	if (obj != null)
	{
		obj.style.display='none';
	}
}

/* 
*	Detail popup handling functions
*/
var myPopupHovering = false;
function HoveringPopup(event, parentObj)
{
	// This will allow the detail window to be relocated
	myPopupHovering = true;
}

function FinishHoveringPopup(event, parentObj)
{
	// This will avoid moving the detail window when it is open
	myPopupHovering = false;
}

function FinishPopupWindow() // ) //, parentObj)
{
	// Change CSS Class
	var obj = document.getElementById('popupdetails');
	obj.className='popupdetails with_border';
}

function disableEventPropagation(myEvent)
{
	/* This workaround is specially for our beloved Internet Explorer */
	if ( window.event)
	{
		window.event.cancelBubble = true; 
	}
}

function movePopupWindow(myEvent, ObjName, parentObj)
{
	var obj = document.getElementById(ObjName);
	
//	var PopupContentWidth = 0;
//	var middle = PopupContentWidth / 2;
	var middle = -10;

	if (myPopupHovering == false && obj != null && parentObj != null)
	{
		// Different mouse position capturing in IE!
		if (szBrowserApp == "IEXPLORER")
		{
			obj.style.top = (event.y+document.body.scrollTop + 10) + 'px';
		}
		else
		{
			obj.style.top = (myEvent.pageY + 20) + 'px';
		}
		obj.style.left = (myEvent.clientX - middle) + 'px';
	}
}

function GoToPopupTarget(myTarget, parentObj)
{
	if (!myPopupHovering)
	{
		// Change document location
		document.location=myTarget;
	}
	else /* Close Popup */
	{
		FinishPopupWindow(parentObj);
	}
}

function HoverPopup( myObjRef, myPopupTitle, HoverContent, OptionalImage )
{
	// Change CSS Class
	var obj = document.getElementById('popupdetails');
	obj.className='popupdetails_popup with_border';

	if ( myObjRef != null)
	{
		myObjRef.src = OptionalImage; 
		// "{BASEPATH}images/player/" + myTeam + "/hover/" + ImageBaseName + ".png";
	}

	// Set title
	var obj = document.getElementById("popuptitle");
	obj.innerHTML = myPopupTitle;

	// Set Content
	var obj = document.getElementById("popupcontent");
	obj.innerHTML = HoverContent;
}

function HoverPopupMenuHelp( myEvent, parentObj, myPopupTitle, HoverContent )
{
	if (szBrowserApp !== "IEXPLORER")
	{
		// Don't need helper here!
		return; 
	}

	// Change CSS Class
	var objPopup = document.getElementById('popupdetails');
	objPopup.className='popupdetails_popup with_border';

	// Set title
	var obj = document.getElementById("popuptitle");
	obj.innerHTML = myPopupTitle;

	// Set Content
	obj = document.getElementById("popupcontent");
	obj.innerHTML = HoverContent;

//	var PopupContentWidth = 0;
//	var middle = PopupContentWidth / 2;
	var middle = -5;

	if (myPopupHovering == false && parentObj != null)
	{
		// Different mouse position capturing in IE!
		objPopup.style.top = (event.y+document.body.scrollTop + 50) + 'px';
		objPopup.style.left = (myEvent.clientX - middle) + 'px';
	}

}

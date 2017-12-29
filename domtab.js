/*
	Function domTab()
	written by Christian Heilmann
*/



function domTab(i){
	// Variables for customisation:
	var numberOfTabs = 5;
	var colourOfInactiveTab = "darkblue";
	var colourOfActiveTab = "silver";
	var colourOfInactiveLink = "silver";
	var colourOfActiveLink = "darkblue";
	// end variables
	if (document.getElementById){
		for (f=1;f<numberOfTabs+1;f++){
			document.getElementById('contentblock'+f).style.display='none';
			//document.getElementById('link'+f).style.background=colourOfInactiveTab;
			document.getElementById('link'+f).style.color=colourOfInactiveLink;
		}
		
		
		document.getElementById('contentblock'+i).style.display='block';
		//document.getElementById('link'+i).style.background=colourOfActiveTab;
		document.getElementById('link'+i).style.color=colourOfActiveLink;
	}
}

//Frop wrote this function
function Tab(){



			if(document.getElementById('gtype').style.display=='none')
			{
			document.getElementById('gtype').style.display='block';
			document.getElementById('gtime').style.display='block';
			}
			else
			{
			document.getElementById('gtype').style.display='none';
			document.getElementById('gtime').style.display='none';
			}

}


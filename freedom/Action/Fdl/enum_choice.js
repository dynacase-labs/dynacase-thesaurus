var notalone=true;
function completechoice(index,tattrid,tattrv,winfo) {
  var rvalue;
  for (i=0; i< tattrid.length; i++) {
      if  (tattrv[index][i].substring(0,1) != '?')  {
	
	if (winfo.document.getElementById(tattrid[i])) {
	  winfo.document.getElementById(tattrid[i]).value = tattrv[index][i].replace(/\\n/g,'\n');

	  winfo.document.getElementById(tattrid[i]).style.backgroundColor='[CORE_BGCOLORHIGH]';
	} else {
	  if (notalone) alert('[TEXT:Attribute not found]'+'['+tattrid[i]+']'+winfo.name);
	}

      } else {
	if ((tattrv[index][i].length > 1) &&
	    ((winfo.document.getElementById(tattrid[i]).value == "") || (winfo.document.getElementById(tattrid[i]).value == " "))) {
	  rvalue = tattrv[index][i].substring(1).replace(/\\n/g,'\n');
	  winfo.document.getElementById(tattrid[i]).value = rvalue;
	  winfo.document.getElementById(tattrid[i]).style.backgroundColor='[CORE_BGCOLORHIGH]';
	}
						      
      }
  }
  winfo.disableReadAttribute();
 
  return;


}

var isNetscape = navigator.appName=="Netscape";





function completechoices() {



    var cvalues = new Array();
    for (i=0; i< tattrid.length; i++) {	
      cvalues[i] ="";
    }
    senum = document.getElementById('schoose');
    for (c=0; c< senum.length; c++) {
      if (senum.options[c].selected) {
	index= senum.options[c].value;
	for (i=0; i< tattrid.length; i++) {
	  with (winfo.document.getElementById(tattrid[i])) {
	    if (tattrv[index][i] != "") {
	      cvalues[i] += tattrv[index][i];
	       cvalues[i] += "\n";
	      style.backgroundColor='[CORE_BGCOLORHIGH]';
	    }
	    //       style.fontWeight='bold';
	  }
	}
      }
    }
    for (i=0; i< tattrid.length; i++) {	
      if (cvalues[i][0] != '?')
	// delete last CR
	winfo.document.getElementById(tattrid[i]).value = cvalues[i].substring(0,cvalues[i].length-1);
    }
    winfo.disableReadAttribute();
}

function autoClose() {
  // see if only one possibility
  if (tattrv.length == 1) {
     completechoice(0,tattrid,tattrv,winfo);     
     setTimeout('self.close()',200); // must be set in next event loop cause Mozilla crash sometimes
  }


 
}


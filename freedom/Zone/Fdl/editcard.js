
// auxilarry window to select choice
var wichoose= false;

function sendmodifydoc(event,docid, attrid, sorm) {


  f =document.modifydoc;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=FDL&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&wname='+window.name;

  
  
  var xy= getAnchorWindowPosition(attrid);

  if (isNaN(window.screenX)){
    xy.y+=15; // add supposed decoration height
    // add body left width for IE sometimes ...
    if (parent.ffolder)  xy.x += parent.ffolder.document.body.clientWidth;
    
  }


  wichoose = window.open('', 'wchoose', 'scrollbars=yes,resizable=yes,height=30,width=290,left='+xy.x+',top='+xy.y);
  wichoose.focus();

  wichoose.moveTo(xy.x, xy.y+10);
  f.target='wchoose';


  
  f.submit();
  // reset to initial action
  f.action=oldact;
  f.target=oldtar;

}

function enableall() {

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) {	
           elements[i].disabled=false;
       }
  }
}


function resizeInputFields() {

  newsize = getFrameWidth() / 15;

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) { 
         if (elements[i].className == 'autoresize') {
	   if (elements[i].type == 'text')
             elements[i].size=newsize;
	   if (elements[i].type == 'textarea')
             elements[i].cols=newsize;
         }
     }
   }
}

// close auxillary window if open
function closechoose() {

    if (wichoose) wichoose.close();
}

function canmodify() {
    var attrNid=[attrnid];
    

    for (var i=0; i< attrNid.length; i++) {	
	if (document.getElementById(attrNid[i])) {
	  if (document.getElementById(attrNid[i]).value == '') {
	    alert('[TEXT:some needed attributes are empty]');
	    return false;
	  }
        }
    }
    return true;
}

// to define which attributes must be disabled
var tain= new Array();
var taout= new Array();
[BLOCK RATTR]
tain[[jska]]=[jstain];
taout[[jska]]=[jstaout];
[ENDBLOCK RATTR]

function disableReadAttribute() {
    
    var ndis = true;
    var i;
    var vin;
    for (var c=0; c< tain.length; c++) {
      ndis = true;
      for (var i=0; i< tain[c].length; i++) {
	    if (document.getElementById(tain[c][i])) {
		vin=document.getElementById(tain[c][i]).value;
		if ((vin == '') || (vin == ' ')) ndis = false;
	    }
	}
      for (var i=0; i< taout[c].length; i++) {
	    if (document.getElementById(taout[c][i])) {
	      if (document.getElementById(taout[c][i]).type != 'hidden') {
		document.getElementById(taout[c][i]).disabled=ndis;		
	      }
	    }
      }
    }
}

function editOnLoad() {
    resizeInputFields();
    disableReadAttribute();
}


function clearInputs(tinput) {
  for (var i=0; i< tinput.length; i++) {
    if (document.getElementById(tinput[i])) {
      document.getElementById(tinput[i]).value=' ';
      document.getElementById(tinput[i]).style.backgroundColor='[CORE_BGCOLORHIGH]';
    }    
  }
  disableReadAttribute();

}

function autoUnlock(docid) {
  if (parseInt(docid) > 0) {
    if (! document.isSubmitted) {
      var fhidden = window.open('','fhidden','');
      fhidden.document.location.href='[CORE_STANDURL]&app=FDL&action=UNLOCKFILE&auto=Y&id='+docid;
    }
  }
}

function pleaseSave(event) {
  if (document.isChanged && (! document.isSubmitted) && (! document.isCancelled)) {
    if (confirm('[TEXT:Save changes ?]')) {
      var bsubmit= document.getElementById('iSubmit');

      var can=bsubmit.onclick.apply(null,[event]);

      if (can) {
	var fedit= document.getElementById('fedit');
	if (fedit.onsubmit) fedit.onsubmit();
	fedit.submit();
      
      } else {
	alert('[TEXT:Data cannot be saved]');
	return false;
      }
    }
  }
  
  return true;
  
}

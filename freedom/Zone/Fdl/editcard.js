
// auxilarry window to select choice
var wichoose= false;

function sendmodifydoc(event,docid, attrid, sorm) {


  f =document.modifydoc;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=FDL&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&wname='+window.name;

  var xw, yw;
  if (window.event) {
      xw = window.event.screenX;
      yw = window.event.screenY;
  } else {
      xw = event.screenX;
      yw = event.screenY;

  }
  with (document.getElementById(attrid))  {
    if (type == 'text')  xw = xw - (size*7) -200;
    else  if (type == 'textarea') xw = xw - (cols*7) -200;
  }
  status = xw +"+"+yw;
  wichoose = window.open('', 'wchoose', 'resizable=yes,height=30,width=40,left='+xw+',top='+yw);
  wichoose.focus();
  wichoose.moveTo(xw, yw);
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
	 if (elements[i].type == 'text')
           elements[i].size=newsize;
	 if (elements[i].type == 'textarea')
           elements[i].cols=newsize;
       }
  }
}

// close auxillary window if open
function closechoose() {

    if (wichoose) wichoose.close();
}

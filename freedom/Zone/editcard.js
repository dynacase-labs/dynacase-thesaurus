


function sendmodifydoc(event,docid, attrid, sorm) {


  f =document.modifydoc;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=[FREEDOM_APP]&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&wname='+window.name;

  f.target='ichoose';

  GetXY(event);
  with (document.getElementById('choose')) {
     style.visibility='visible';
     style.top = Ypos;
     style.left = Xpos-100;
  }
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

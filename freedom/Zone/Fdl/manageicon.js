

var oldBgcolor=0;
var oldBdstyle=0;
function highlight(th) {


  th.className="select";
  //oldBgcolor=th.style.backgroundColor;
  //oldBdstyle=th.style.borderStyle;
  //th.style.backgroundColor='[CORE_BGCOLORALTERN]';
  //th.style.borderStyle='solid';
  
}
function unhighlight(th) {
  th.className="unselect";
  //th.className="icon";
  //th.style.backgroundColor='[CORE_BGCELLCOLOR]';
  //th.style.borderStyle='none';
  //th.style.backgroundColor=oldBgcolor;
    //  th.style.borderStyle=oldBdstyle;
}

var isNetscape = navigator.appName=="Netscape";

var docid = 1;




// align automatically icon with screen width 
function placeicons(dy) {

  if (! dy) dy=30;
      winW=getFrameWidth();
	nbicons=[nbdiv];
	nbcol = Math.floor(winW/60);
	if (nbcol < 1) nbcol=1;

 	for (i=1; i <= nbicons; i++) {
         div = document.getElementById('d'+i);

	 div.style.left = ((i-1)%nbcol) * (div.offsetWidth);
	 div.style.top = Math.floor((i-1)/nbcol)*(div.offsetHeight) + dy;
	 div.style.visibility = 'visible';
	}
  }
var diva=document.getElementById('a1');


// select document
function select(th, id, divid) {

  if (diva) {
      diva.style.visibility='hidden';
  }

  if (selobjid)  unhighlight(document.getElementById(selobjid));
  highlight(th);
  docid=id;
  document.docid = docid;
  selid = divid;
  selobjid = th.id;
  imgid="i"+divid;
}


function viewabstract(event) {
    
  
  diva = document.getElementById('a'+selid);
  if (!diva) return;
  div = document.getElementById('d'+selid);
  

      //diva.innerHTML = diva.innerHTML;

      //alert('diva');
      diva.style.visibility='visible';


	if ((div.offsetLeft+div.offsetWidth-10 + diva.offsetWidth) > getFrameWidth()) 
	     diva.style.left = div.offsetLeft-diva.offsetWidth+10;         
	else diva.style.left = div.offsetLeft+div.offsetWidth-10;

	if ((div.offsetTop+div.offsetHeight-60 + diva.offsetHeight) > getFrameHeight()) 
	     diva.style.top = div.offsetTop-diva.offsetHeight;
	else diva.style.top = div.offsetTop+div.offsetHeight-60;
      diva.style.zIndex = 5;
       
}

function openMenuOrAbstract(event) {
  if (window.event) {
	shiftKey = window.event.shiftKey
	button=window.event.button;
   } else  {
	shiftKey = event.shiftKey
	button= event.button +1;
}
  window.status=shiftKey+"/"+button;

  if (button == 1) {
    if (shiftKey ) {
      openMenu(event,'popup');
     } else {
      viewabstract(event)
    }
  }

}


function openMenuOrProperties(event,menuid,itemid,target) {
  if (window.event) {
    shiftKey = window.event.shiftKey
      button=window.event.button;
  } else  {
    shiftKey = event.shiftKey
      button= event.button +1;
  }
  window.status=shiftKey+"/"+button;


  if (! docTarget) docTarget='fdoc';
  if (button == 1) {
    if (shiftKey ) {
      openMenu(event,menuid, itemid);
    } else {
      subwindow(300,400,docTarget,'[CORE_STANDURL]&app=FDL&action=FDL_CARD&props=N&abstract=N&id='+docid);
    }
  }

}

function sendFirstFile(docid) {
  url='[CORE_STANDURL]&app=FDL&action=EXPORTFIRSTFILE&docid='+docid;

  we = window.open('about:blank','','resizable=yes,scrollbars=yes');
  we.document.location.href=url;
}

function openFld(docid) {
  url='[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_VIEW&dirid='+docid;
  subwindow(300,400,'flist',url);
}
//--------------------- DRAG & DROP  --------------------------
drag=0;

if (isNetscape) {
    document.captureEvents(Event.MOUSEMOVE);
    document.captureEvents(Event.KEYPRESS)
}    


//document.onmousemove = GetXY;;

document.onkeypress = trackKey;

function trackKey(event)
{
  var intKeyCode;

  if (isNetscape) {
    intKeyCode = event.which;
  altKey = event.altKey
   }  else {
    intKeyCode = window.event.keyCode;
    altKey = window.event.altKey;
    }
  
  window.status=intKeyCode + ':'+altKey;
  if ( (intKeyCode == 99)) { // Alt-C key
         activedrag(event); 
  return false;
  } else
    return true;
}



function moveicon(event) {
    
//    window.status="drag="+document.drag;
  if (drag) {
    GetXY(event);
    micon.style.top = Ypos+2; 
    micon.style.left = Xpos+2; 
  }
}

var micon;
function initmicon() {
    micon = document.getElementById('micon');

	}

  

var selid=0; // selected object
var imgid=0;
var selobjid=0; // HTML object selected
function activedrag(event)
{

  document.onmousemove= moveicon;



  drag=1;
    micon.src=document.getElementById(imgid).src;


    GetXY(event);
    window.status=Xpos+"+"+Ypos;
    micon.style.visibility = 'visible';
    micon.style.top = Ypos+2; 
    micon.style.left = Xpos+2; 
    micon.style.zIndex = 14; 
    //document.body.style.cursor='move';
  return false;
}
function deactivedrag(th)
{
  document.onmousemove= "";
  drag=0;
    document.body.style.cursor='auto';
  return true;
}

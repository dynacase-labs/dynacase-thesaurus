var isNetscape = navigator.appName=="Netscape";

var Xpos = 0;
var Ypos = 0;


function GetXY(event) {
  if (window.event) {
    Xpos = window.event.clientX + document.documentElement.scrollLeft
                             + document.body.scrollLeft;
    Ypos = window.event.clientY + document.documentElement.scrollTop +
                             + document.body.scrollTop;
  }
  else {
    Xpos = event.clientX + window.scrollX;
    Ypos = event.clientY + window.scrollY;
  }    
}
function changeClass(th, name)
{ th.className=name;return true}



// return true is shift key is pushed
function shiftKeyPushed(event) {

  if (window.event) shiftKey = window.event.shiftKey	
    else shiftKey = event.shiftKey	

  return shiftKey;
}


// 1 for first : 1 | 2 | 3
function buttonNumber(event) {
  if (window.event) return button=window.event.button;
  else return button= event.button +1;
}

function openMenu(event, menuid) {

  var el, x, y;

  el = document.getElementById(menuid);
  if (window.event) {
    x = window.event.clientX + document.documentElement.scrollLeft
                             + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop +
                             + document.body.scrollTop;
  }
  else {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }
  x -= 2; y -= 2;
  el.style.left = x + "px";
  el.style.top  = y + "px";
  el.style.visibility = "visible";

  // active css for animation
    for (i=0; i<nbmitem; i++) {

      mitem = document.getElementById(tdivid[i]);
      if (tdiv[selid][i] == 1) {
	mitem.className='menuItem';
	
      } else      if (tdiv[selid][i] == 2) {
	mitem.className='menuItemInvisible';
	
      }else {
	mitem.className = 'menuItemDisabled';
      } 
    }
  return false; // no navigator context menu
}




function closeMenu(menuid) {

  if (document.getElementById) { // DOM3 = IE5, NS6
         divpop = document.getElementById(menuid);
	 divpop.style.visibility = 'hidden';
   }    
}

function activate(th, url, wname) {
  if (th.className == 'menuItem') {
        subwindow(300,400,wname,url);
	//document.location.href=url;
	//	closeMenu();
  }
}

function sendandreload(th, url) {
  if (th.className == 'menuItem') {
        subwindow(300,400,'doc_properties',url);
	//	closeMenu();
	//if (window.name != 'doc_properties')
	//  document.location.reload(true);
  }
}

var nbmitem =[nbmitem]; 
var tdiv= new Array([nbdiv]+1);
tdivid=[menuitems];
[BLOCK MENUACCESS]
tdiv[[divid]]=[vmenuitems];
[ENDBLOCK MENUACCESS]


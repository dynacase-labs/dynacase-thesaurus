var isNetscape = navigator.appName=="Netscape";

var Xpos = 0;
var Ypos = 0;



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

function getScrollYOffset() {
  if (document.all) return document.body.scrollTop;
  else return window.pageYOffset;
}





function openMenu(event, menuid, itemid) {

  var el, x, y;

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


  el = document.getElementById(menuid);
  el.style.left = x + "px";
  el.style.top  = y + "px";
  el.style.visibility = "visible";

  activeMenuItem(menuid, itemid);
  return false; // no navigator context menu
}


function openMenuXY(event, menuid, x, y) {

  var el;



  el = document.getElementById(menuid);
  el.style.left = x + "px";
  el.style.top  = y + "px";
  el.style.visibility = "visible";

  activeMenuItem(menuid, 1); // first item (no context : only one item)
  return false; // no navigator context menu
}

function activeMenuItem(menuid, itemid) {
  window.status="menu:"+menuid+itemid;
  // active css for animation for 'selid' object
    for (i=0; i<nbmitem[menuid]; i++) {

      mitem = document.getElementById(tdivid[menuid][i]);
      if (tdiv[menuid][itemid][i] == 1) {
	mitem.className='menuItem';
	
      } else      if (tdiv[menuid][itemid][i] == 2) {
	mitem.className='menuItemInvisible';
	
      }else {
	mitem.className = 'menuItemDisabled';
      } 
    }
  
}


function closeMenu(menuid) {

  if (document.getElementById) { // DOM3 = IE5, NS6
         divpop = document.getElementById(menuid);
	 divpop.style.visibility = 'hidden';
   }    
  return false;
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

function closeAllMenu() {
[BLOCK CMENUS]  closeMenu('[name]');
[ENDBLOCK CMENUS]
}
var tdiv= new Array();
var tdivid= new Array();
var nbmitem= new Array();
[BLOCK MENUS]
nbmitem['[name]'] =[nbmitem]; 
tdiv['[name]']= new Array([nbdiv]);
tdivid['[name]']=[menuitems];
[ENDBLOCK MENUS]

[BLOCK MENUACCESS]
tdiv['[name]'][[divid]]=[vmenuitems];
[ENDBLOCK MENUACCESS]


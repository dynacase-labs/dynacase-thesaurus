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

// return true is shift key is pushed
function ctrlKeyPushed(event) {

  if (window.event) ctrlKey = window.event.ctrlKey	
    else ctrlKey = event.ctrlKey	

  return ctrlKey;
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


function openSubMenu(event, th, menuid) {
  var xy=getAnchorPosition(th.id);
  var dx=th.parentNode.offsetWidth;

  // close sub menu before
  closeSubMenu(th.parentNode.id);

  

  openMenuXY(event,menuid,xy.x+dx,+xy.y);
}

function closeSubMenu(menuid) {  
 
  var sm = document.body.getElementsByTagName('div');
 
  for (var i=0; i<sm.length; i++) {
    //alert(sm[i].id+':'+sm[i].getAttribute('name'));
    if (sm[i].getAttribute('name') == menuid)    closeMenu(sm[i].id);
  }
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
  closeSubMenu(menuid);
  activeMenuItem(event,menuid, itemid);
 // event.stopPropagation();
  return false; // no navigator context menu
}


function openMenuXY(event, menuid, x, y) {

  var el;



  el = document.getElementById(menuid);
  if (!(isNetscape && (el.style.position=='fixed'))) {

    el.style.top  = y + "px";
  }
  el.style.left = x + "px";
  el.style.visibility = "visible";

  activeMenuItem(event,menuid, 1); // first item (no context : only one item)
  return false; // no navigator context menu
}

function activeMenuItem(event,menuid, itemid) {
  window.status="menu:"+menuid+itemid;
  // active css for animation for 'selid' object
    for (i=0; i<nbmitem[menuid]; i++) {

      //      alert(tdivid[menuid][i]);
      mitem = document.getElementById(tdivid[menuid][i]);
      if (tdiv[menuid][itemid][i] == 1) {
	mitem.className='menuItem';
	
      } else      if (tdiv[menuid][itemid][i] == 2) {
	mitem.className='menuItemInvisible';
	
      }else   if (tdiv[menuid][itemid][i] == 3) {
	if (ctrlKeyPushed(event)) mitem.className='menuItemCtrl';
	else  mitem.className='menuItemInvisible';
	
      }else  if (tdiv[menuid][itemid][i] == 4) {
	if (ctrlKeyPushed(event)) mitem.className='menuItemCtrlDisabled';
	else  mitem.className='menuItemInvisible';
	
      }else {
	mitem.className = 'menuItemDisabled';
	mitem.onclick= function () {closeMenu(menuid);}
      } 
    }
  
}


function closeMenu(menuid) {
  //  alert('closeMenu:'+menuid);
  closeSubMenu(menuid);
  if (document.getElementById) { // DOM3 = IE5, NS6
         divpop = document.getElementById(menuid);
	 if (divpop) divpop.style.visibility = 'hidden';
   }    
  return false;
}

function activate(th, url, wname) {
  if ((th.className == 'menuItem') || (th.className == 'menuItemCtrl')) {
    // add referer url for client doesn't not support it
    //  var urlref;
  //   if (isNetscape) urlref=url;
//     else urlref= url+'&http_referer='+escape(window.location.href);
    if (wname == "") window.location.href=url;
    else subwindowm(300,400,wname,url);
   
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

var tdiv= new Array();
var tdivid= new Array();
var nbmitem= new Array();


var CTRLKEYMENU=false;
var INPROGRESSMENU=false;
var MENUCIBLE=null;
var MENUSOURCE=null;
var MENUREQ=null;
var MENUOUT=true;
var MENUOUTTIMER=false;

var DIVPOPUPMENU=document.createElement("div");

addEvent(window,"load",function adddivpop() {document.body.appendChild(DIVPOPUPMENU)});
function reqViewMenu() {
  INPROGRESSMENU=false; 
  document.body.style.cursor='auto';
  var o=MENUCIBLE;
 
  if (MENUREQ.readyState == 4) {
    // only if "OK"
    //dump('readyState\n');
    if (MENUREQ.status == 200) {
      // ...processing statements go here...
      //  alert(MENUREQ.responseText);
      if (MENUREQ.responseXML) {
	var elts = MENUREQ.responseXML.getElementsByTagName("status");

	if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var delay=elt.getAttribute("delay");
	  var c=elt.getAttribute("count");
	  var w=elt.getAttribute("warning");

	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+MENUREQ.responseText);
	    return;
	  }
	  elts = MENUREQ.responseXML.getElementsByTagName("branch");
	  elt=elts[0].firstChild.nodeValue;
	  // alert(elt);
	  if (o) {
	    if (c > 0)       o.style.display='';
	    o.style.left = 0;
	    o.style.top  = 0;
	    o.innerHTML=elt;
	    openDocMenu(false,'popupdoc');
	  }
	  
	} else {
	  alert('no status\n'+MENUREQ.responseText);
	  return;
	}
      } else {
	alert('no xml\n'+MENUREQ.responseText);
	return;
      } 	  
    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    MENUREQ.statusText);
      return;
    }
  } 
}

function menuSend(event,menuurl,cible) {
  if (INPROGRESSMENU) return false; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        MENUREQ = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      isIE = true;
      MENUREQ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (MENUREQ) {
        MENUREQ.onreadystatechange = reqViewMenu ;
        MENUREQ.open("POST", menuurl,true); //'index.php?sole=Y&app=FDL&action=POPUPDOCDETAIL&id='+docid, true);
	MENUREQ.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	MENUCIBLE=cible;


	MENUREQ.send(null);
	
	
	INPROGRESSMENU=true;
	document.body.style.cursor='progress';	
	GetXY(event);
	cible.style.left = Xpos;
	cible.style.top  = Ypos;
	cible.style.width  = '30px';
	//	clipboardWait(cible);
	return true;
    }    
}

function viewmenu(event,docid,source) {
  closeDocMenu()
  CTRLKEYMENU=ctrlKeyPushed(event);
  MENUSOURCE=source;
  GetXY(event);
  XMENU=Xpos;
  YMENU=Ypos;
  //   MENUSOURCE.style.borderStyle='solid';
  // MENUSOURCE.style.borderColor='black';
  //MENUSOURCE.style.borderWidth='1px';

  if (MENUSOURCE) {
    MENUSOURCE.style.borderTop='dashed 1px #777777';
    MENUSOURCE.style.borderBottom='dashed 1px #777777';
  }
  menuSend(event,docid,DIVPOPUPMENU);
}

function closeDocMenu() {
  var o =DIVPOPUPMENU;
  if (o) o.style.display='none';
  if (MENUSOURCE) {
    MENUSOURCE.style.borderTop='';
    MENUSOURCE.style.borderBottom='';
  }
}
function sendMenuUrl(th, url, wname,bar) {
  if ((th.className == 'menuItem') || (th.className == 'menuItemCtrl')) {
    // add referer url for client doesn't not support it
    //  var urlref;
  //   if (isNetscape) urlref=url;
//     else urlref= url+'&http_referer='+escape(window.location.href);

    if ((wname == "")||(wname == "_self")) {
      setTimeout('viewwait()',1000);      
      window.location.href=url;
    } else {
      if (bar) subwindowm(fdl_vd2size,fdl_hd2size,wname,url);
      else subwindow(fdl_vd2size,fdl_hd2size,wname,url);
    }
   
  }
}
// return true is ctrl key is pushed
function ctrlKeyPushed(event) {

  if (window.event) ctrlKey = window.event.ctrlKey	
  else ctrlKey = event.ctrlKey	

  return ctrlKey;
}
function openDocMenu(event, menuid) {
  var el, x, y;
  var cy,h1,hf;
  

  x=XMENU;y=YMENU;
  if ((x==0) && (y==0)) {
    x=Xold;
    y=Yold;
    if ((x==0) && (y==0)) {x=100;y=100;}
  }

  x -= 2; y -= 2;



  //  closeSubMenu(menuid);
  activeMenuDocItem(event,menuid);

  el = document.getElementById(menuid);
  el.style.left = "0px";
  el.style.top  = y + "px";
  //el.style.width  =  "100%";
  el.style.visibility = "hidden";


  // complete sub menus
  


  // test if it is on right of the window
  w2=getObjectWidth(document.body);
      // display right or left to maximize width
  w1=getObjectWidth(el);

      x2=x;
      if (x+w1 > w2) {
	if (w1<w2) {
	  x2=w2-w1;
	} else {
	  x2=0;
	}
      } 

  cy=(window.event)?window.event.clientY:event.clientY;
  h1=getObjectHeight(el);
  hf=getFrameHeight();
  if (cy+h1 > hf) {
    y=y-h1+4;
    if (cy-h1 < 0) y=0;
  }
  if (h1 > hf) y=0;
    el.style.left = x2 + "px";
    el.style.top  = y + "px";
    el.style.display = "none";
    el.style.display = "";
    el.style.visibility = "visible";


 // event.stopPropagation();
  return false; // no navigator context menu
}
function openSubDocMenu(event, th, menuid) {
  var xy=getAnchorPosition(th.id);
  var dx=th.parentNode.offsetWidth;
  var el,cy,hf,hh;
  var x1,x2,w1,w2,dw;
  var x=xy.x;
  var y=xy.y;

  el=document.getElementById(menuid);
  // close sub menu before
  // closeSubMenu(th.parentNode.id);
  

  el = document.getElementById(menuid);
  w1=getObjectWidth(el);
  w2=getObjectWidth(document.body);
  x2=x+dx;
  if (x+w1+dx > w2) {
	if (w1<w2) {
	  x2=x-w1;
	} 
  } 

  cy=(window.event)?window.event.clientY:event.clientY;
  hf=getFrameHeight();
  h1=getObjectHeight(el);
  hh=getObjectHeight(th);
  //  alert(h1+'-'+cy+'-'+hf+'-'+xy.y);
  if (cy+h1>hf) y=y-h1+hh;

  //  openMenuXY(event,menuid,x2,y);
    el.style.top = y + "px";
    el.style.left = x2 + "px";
    el.style.display = "none";
    el.style.display = "";
    el.style.visibility = "visible";
}
function menuover() {
  window.status='menuover';
  MENUOUT=false;
  if (MENUOUTTIMER) window.clearTimeout(MENUOUTTIMER);
  MENUOUTTIMER=false;
}

function menuout() {
  window.status='menuout';
  MENUOUTTIMER=window.setTimeout('closeDocMenu()',1500);

}
function activeMenuDocItem(event,menuid) {
  //window.status="menu:"+menuid+itemid;
  // active css for animation for 'selid' object
  var o=document.getElementById(menuid);
  if (o) {
    var ta=o.getElementsByTagName("a");
    var mitem;
    var submenu;
    var menuitem;

    for (var i=0; i<ta.length; i++) {

      //      alert(tdivid[menuid][i]);
      mitem = ta[i];
      visibility=mitem.getAttribute('visibility');
      if (visibility == 1) {
	mitem.className='menuItem';
	
      } else      if (visibility == 2) {
	mitem.className='menuItemInvisible';
	
      } else   if (visibility == 3) {
	if (CTRLKEYMENU) mitem.className='menuItemCtrl';
	else  mitem.className='menuItemInvisible';
	
      } else  if (visibility == 4) {
	if (CTRLKEYMENU) mitem.className='menuItemCtrlDisabled';
	else  mitem.className='menuItemInvisible';
	
      } else if (visibility == 0) {
	mitem.className = 'menuItemDisabled';
	mitem.onclick= function () {closeDocMenu();}
      } 
      //      mitem.onmouseover=menuover;
      addEvent(mitem,'mouseover',menuover);
      addEvent(mitem,'mouseout',menuout);
    }    
    
    // complete sub menu
    for (var i=0; i<ta.length; i++) {

      mitem = ta[i];      
      submenu=mitem.getAttribute('submenu');
      if (submenu != "") {
	sdiv=document.getElementById('popup'+submenu);
	if (sdiv) {
	   sdiv.appendChild(mitem);
	   menuitem=document.getElementById(submenu);
	   if (menuitem) {
	     if (mitem.className=='menuItem') menuitem.className='menuItem';
	     else if (CTRLKEYMENU && (mitem.className!='menuItemInvisible')) menuitem.className='menuItem';
	   }
	}
      }
    }

  }
  
}

var isNetscape = navigator.appName=="Netscape";




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
var Xold; // for short cut key
var Yold;
function openMenu(event, menuid, itemid) {
  var el, x, y;

  GetXY(event);
  if ((Xpos>0) && (Ypos>0)) {
   Xold=Xpos;
   Yold=Ypos;
  }
  x=Xpos;y=Ypos;
  if ((x==0) && (y==0)) {
    x=Xold;
    y=Yold;
    if ((x==0) && (y==0)) {x=100;y=100;}
  }

  x -= 2; y -= 2;


  el = document.getElementById(menuid);
  el.style.left = x + "px";
  el.style.top  = y + "px";
  el.style.visibility = "visible";
  el.style.display = "";
  closeSubMenu(menuid);
  activeMenuItem(event,menuid, itemid);
 // event.stopPropagation();
  return false; // no navigator context menu
}
document.write('<img id="WIMG" src="Images/gyro.gif" style="display:none;position:absolute">');
function viewwait() {
  var wimgo = document.getElementById('WIMG');
  if (wimgo) {
    wimgo.style.display='inline';
    CenterDiv(wimgo.id);
  }
}

function openMenuXY(event, menuid, x, y) {

  var el,menudiv;

  var x1,x2,w1,w2,dw;
  var bm=document.getElementById('barmenu');
  el = document.getElementById(menuid);
  if (el) {
    if (isNetscape && (el.style.position=='fixed')) {
      y -= getScrollYOffset();
    } 
    activeMenuItem(event,menuid, 1); // first item (no context : only one item)
    el.style.display = "none";
    el.style.top  = y + "px";
    el.style.left = "0px";
    el.style.visibility = "hidden";
    el.style.display = "";
    if (bm) {
      w2=getObjectWidth(document.getElementById('barmenu'));
      // display right or left to maximize width
      w1=getObjectWidth(el);


      if (x+w1 > w2) {
	if (w1<w2) {
	  x2=w2-w1;
	} else {
	  x2=0;
	}
      } else {
	x2=x;
      }

    } else {
      x2=x;
    }
    el.style.left = x2 + "px";
    el.style.display = "none";
    el.style.display = "";
    el.style.visibility = "visible";
  }
  return false; // no navigator context menu
}
var menusel=null;
function selectMenu(th) {
  unSelectMenu();
  th.className='MenuSelected';
  menusel=th;  
}
function unSelectMenu() {
  var bm=document.getElementById('barmenu');
  if (bm) {
    var ttd=bm.getElementsByTagName("td");
    for (var i=0;i<ttd.length;i++) {
      if (ttd[i].className=='MenuSelected')  ttd[i].className='MenuInactive';
    }
  }
}
function ActiveMenu(th) {
  if (th.className!='MenuSelected') th.className='MenuActive';
}
function DeactiveMenu(th) {
  if (th.className!='MenuSelected')  th.className='MenuInactive';  
}
function activeMenuItem(event,menuid, itemid) {
  //window.status="menu:"+menuid+itemid;
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
	 if (this.className == 'MenuSelected') this.className='MenuInactive';
   }    
  return false;
}

function activate(th, url, wname,bar) {
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

function sendandreload(th, url) {
  if (th.className == 'menuItem') {
        subwindow(fdl_vd2size,fdl_hd2size,'doc_properties',url);
	//	closeMenu();
	//if (window.name != 'doc_properties')
	//  document.location.reload(true);
  }
}

var tdiv= new Array();
var tdivid= new Array();
var nbmitem= new Array();


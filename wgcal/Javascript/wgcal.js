
function eltId(eltid) {
  if (document.getElementById(eltid)) return document.getElementById(eltid);
  return false;
}

function fcalSetOpacity(o, value) {
// 	o.style.opacity = value/100;
// 	o.style.filter = 'alpha(opacity=' + value + ')';
}


function showWaitServerMessage(ev, msg) {
  globalcursor('progress');
//   fcalSetOpacity(document.body, 40);
//   if (document.getElementById('waitmessage')) {
//     var ws = eltId('waitmessage'); 
//     if (msg) eltId('wmsgtext').innerHTML = msg;
//     if (!ev) {
//       var xm = posM.x;
//       var ym = posM.y;
//     } else {
//       var xm = getX(ev);
//       var ym = getY(ev);
//     }
//     computeDivPosition('waitmessage',xm, ym, 10);
//   }
}

function hideWaitServerMessage() {
//   if (document.getElementById('waitmessage')) {
//     var ws = eltId('waitmessage'); 
//     ws.style.display = 'none';
//   }
//   fcalSetOpacity(document.body, 100);
  unglobalcursor();
}


var  CGCURSOR='auto'; // current global cursor

function globalcursor(c) {
  if (c==CGCURSOR) return;
  if (!document.styleSheets) return;
  unglobalcursor();
  document.body.style.cursor=c;
  if (document.styleSheets[1].addRule) {
    document.styleSheets[1].addRule("*","cursor:"+c+" ! important",0);
  } else if (document.styleSheets[1].insertRule) {
    document.styleSheets[1].insertRule("*{cursor:"+c+" ! important;}", 0);
  }
  CGCURSOR=c;
}
function unglobalcursor() {
  if (!document.styleSheets) return;
  var theRules;
  var theSheet;
  var r0;
  var s='';
  
  document.body.style.cursor='auto';
  
  theSheet=document.styleSheets[1];
  if (document.styleSheets[1].cssRules)
    theRules = document.styleSheets[1].cssRules;
  else if (document.styleSheets[1].rules)
    theRules = document.styleSheets[1].rules;
  else return;
  
  r0=theRules[0].selectorText;
  /* for (var i=0; i<theSheet.rules.length; i++) {
     s=s+'\n'+theSheet.rules[i].selectorText;
     s=s+'-'+theSheet.rules[i].style;
     }*/
  //  alert(s);
  
  if ((r0 == '*')||(r0 == '')) {
    
    if (document.styleSheets[1].removeRule) {
      document.styleSheets[1].removeRule(0);
    } else if (document.styleSheets[1].deleteRule) {
      document.styleSheets[1].deleteRule(0);
    }
  }
  CGCURSOR='auto';;
} 


function  fcalGetJSDoc(ev, id) {
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_DOCGETVALUES&id="+id;
  
  showWaitServerMessage(ev,'Loading event');
  var res;
  res = fcalSendRequest(urlsend, false, false);
  if (res.status!=200) return false;
  hideWaitServerMessage();
  eval(res.content);
  if (fcalStatus.code==-1) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  } else {
    return docValues;
  }
}
  

function computeDivPosition(o, xm, ym, delta, yratio) {

  if (!eltId(o)) {
    alert('Element '+o+' not found');
    return;
  }
  var eid = eltId(o);

  if (!yratio) yratio=1.0;

  eid.style.position = 'absolute';
  eid.style.left = '20px';
  eid.style.top = '20px';
  eid.style.visibility = 'hidden';
  eid.style.display = 'block';

  var ww = getFrameWidth();
  var wh = getFrameHeight();
  var h  = getObjectHeight(eid);
  var w  = getObjectWidth(eid);
  if (w>parseInt(ww*yratio)) {
    w = parseInt(ww*yratio);
    eid.style.width = w;
  }
  var w1 = xm;
  var w2 = ww - xm;
  var h1 = ym;
  var h2 = wh - ym;
  
  var xp = yp = 0;
   if (w < (w2+delta)) xp =  xm + delta;
  else if (w < (w1+delta)) xp = xm - delta - w;
  else xp = delta;

  if (h < (h2+delta)) yp = ym + delta;
  else if (h < (h1+delta)) yp = ym - delta - h;
  else yp = delta;

  eid.style.left = parseInt(xp)+'px';
  eid.style.top = parseInt(yp)+'px';
  eid.style.visibility = 'visible';

  return;
}
 



function clickB(idb,frombutton) {
  var eb = document.getElementById(idb);
  if (!eb) return false;
  if (eb.type=='radio') {
    if (!frombutton && eb.type=='radio' && eb.checked) return false;
    eb.checked = (eb.checked ? "" : "checked" );
  } else {
    eb.checked = (eb.checked ? "" : "checked" );
  }
  return true;
}

// --------------------------------------------------------
function getX(e) { 
  var posx = 0; 
  if (!e) var e = window.event;
  if (e.pageX) posx = e.pageX;
  else if (e.clientX) posx = e.clientX + document.body.scrollLeft;
  return posx;
}

// --------------------------------------------------------
function getY(e) { 
  var posy = 0; 
  if (!e) var e = window.event;
  if (e.pageY)  posy = e.pageY;
  else if (e.clientY)  posy = e.clientY + document.body.scrollTop;
  return posy;
}


function setDaysViewed(ndays) {
  usetparam(-1, "WGCAL_U_VIEW", "week", '', '');
  usetparam(-1, "WGCAL_U_DAYSVIEWED", ndays, 'wgcal_calendar', '[CORE_STANDURL]&app=WGCAL&action=WGCAL_CALENDAR');
}
function setTextView(sh) {
  usetparam(-1, "WGCAL_U_VIEW", "text", '', '');
  p = '';
  v = '';
  if (sh>0) {
    p = "WGCAL_U_CALCURDATE";
    v = sh;
  }
  usetparam(-1, p, v, 'wgcal_calendar', '[CORE_STANDURL]&app=WGCAL&action=WGCAL_TEXTMONTH');
}


// --------------------------------------------------------
function Fade(elt, size, css) {
  elt.width += size;  
  elt.height += size;
  elt.className = css;
}
function UnFade(elt, size, css) {
  elt.width -= size;  
  elt.height -= size;
  elt.className = css;
}


// --------------------------------------------------------
function  mynodereplacestr(n,s1,s2) {
  
  var kids=n.childNodes;
  var ka;
  var avalue;
  var regs1;
  var rs1;
  var tmp;
  var attnames = new Array('style', 'title', 'src' , 'onclick', 'href','onmousedown','onmouseout', 'onmouseover','id','name','onchange');
  // for regexp
  rs1 = s1.replace('[','\\[');
  rs1 = rs1.replace(']','\\]');
  regs1 = new RegExp(rs1,'g');
  
  for (var i=0; i< kids.length; i++) {     
    if (kids[i].nodeType==3) { 
      // Node.TEXT_NODE
      
	if (kids[i].data.search(rs1) != -1) {
	  tmp=kids[i].data; // need to copy to avoid recursive replace
	  
	  kids[i].data = tmp.replace(s1,s2);
	}
    } else if (kids[i].nodeType==1) { 
      // Node.ELEMENT_NODE
	
	// replace  attributes defined in attnames array
	  for (iatt in attnames) {
	    
	    attr = kids[i].getAttributeNode(attnames[iatt]);
	    if ((attr != null) && (attr.value != null) && (attr.value != 'null'))  {
	      
	      if (attr.value.search(rs1) != -1) {				
		avalue=attr.value.replace(regs1,s2);

		if (isNetscape) attr.value=avalue;
		else if ((attr.name == 'onclick') || (attr.name == 'onmousedown') || (attr.name == 'onmouseover')) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
		else attr.value=avalue;
	      }
	    }
	  }
      mynodereplacestr(kids[i],s1,s2);
    } 
  }
}

// --------------------------------------------------------
function WGCalImgAltern(ev, eltId, img1, img2) {
  var elt = document.getElementById(eltId);
  var result;
  if (!elt) {
    window.status = "Element["+eltId+"] not found";
    return;
  }
  var sea = new String(elt.src);
  if (sea.indexOf(img1) != -1) {
    elt.src = img2;
  } else {
    elt.src = img1;
  }
}
 
function fcalChangeUPref(uid, pname, pvalue, paction, jspost) {
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_USETPARAM&uid="+uid+"&pname="+pname+"&pvalue="+escape(pvalue);
 fcalSendRequest(urlsend, false, false);

}

function changeUPref(uid, name, value, target, taction) {
   usetparam(uid, name, value, target, taction);
}

function mytoto(name, value, target, taction)
 {
   usetparam(-1, name, value, target, taction);
}

// --------------------------------------------------------
function usetparam(uid, name, value, updatetarget, updateaction) 
{
  fset = document.getElementById('usetparam');
  taction = document.getElementById('taction');
  if (name!='') {
    document.getElementById('uid').value = uid;
    document.getElementById('pname').value = name;
    document.getElementById('pvalue').value = value;
  }
  if (updatetarget=='') {
    updatetarget = 'wgcal_hidden';
    updateaction = 'WGCAL_HIDDEN';
  }
  taction.value = updateaction;
  fset.target = updatetarget;

  fset.submit();
}


var isNetscape = navigator.appName=="Netscape";


// --------------------------------------------------------

function WGCalChangeVisibility(tool, iclose, iopen) {
  el = document.getElementById('v'+tool);
  bel = document.getElementById('b'+tool);
  oel = document.getElementById('o'+tool);
  if (el.style.display=='') {
    el.style.display = 'none';
    if (bel) bel.className = 'wToolButtonSelect';
    if (iopen && oel) oel.src = iclose;
  } else { 
    el.style.display = '';
    if (bel) bel.className = 'wToolButtonUnselect';
    if (iopen && oel) oel.src = iopen;
  }
  WGCalSaveToolsVisibility();
  return;
}

function WGCalSaveToolsVisibility() {
  var s='';
  var i=0;
  for (i=0; i<toolList.length; i++) {
    el = document.getElementById('v'+toolList[i]);
    if (el) {
      v = (el.style.display == '' ? 1 : 0 );
      s += (s==''?'':'|');
      s +=  toolList[i]+'%'+v;
    }
  }
  fcalChangeUPref(-1,'WGCAL_U_TOOLSSTATE', s); 
}


// --------------------------------------------------------
function fcalSendRequest(url, sync, post, noreturn) {

  if (window.XMLHttpRequest) sreq = new XMLHttpRequest();
  else sreq = new ActiveXObject("Microsoft.XMLHTTP");
  if (sreq) {
    if (!sync) {
      sreq.open("POST", url, false);
      sreq.send('');
      if (noreturn) return;
      var result = { request:'', status:0, content:'' };
      result.status = sreq.status;
      result.content = sreq.responseText;
      return result;
    } else {
      sreq.onreadystatechange =  function() {
	if (sreq.readyState == 4) {
	  if (noreturn) return;
	  var result = { request:'', status:0, content:'' };
 	  result.status = sreq.status;
 	  result.content = sreq.responseText;
 	  if (post) post(result);
	}
      }
      sreq.open("POST", url, true);
      sreq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      sreq.send('');
    }
  } else alert('error req');
  return;
}    


// --------------------------------------------------------
function loadPeriod(urlroot, ts) {
    usetparam(-1, "WGCAL_U_CALCURDATE", ts, 'wgcal_calendar', urlroot+'&app=WGCAL&action=WGCAL_CALENDAR');
}



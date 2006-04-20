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
 
function fcalChangeUPrefDbg() {
  var argv = fcalChangeUPrefDbg.arguments;
  var argc = argv.length;
  var sdb = 'fcalChangeUPrefDbg(';
  for (var i = 0; i < argc; i++) {
    sdb = sdb + '\n argv['+i+'] = {'+argv[i] + '}';
  }
  sdb += '\n)';
  alert(sdb);
}

function fcalChangeUPref(uid, pname, pvalue, paction, jspost) {
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.uid = uid;
  rq.pname = pname;
  rq.pvalue = pvalue;
  rq.paction = paction;
  rq.jspost = jspost;
  rq.onreadystatechange =  function() {
    if (rq.readyState == 4) {
      if (rq.responseText && rq.status==200) {
	if (rq.jspost) rq.jspost(rq.uid, rq.pname, rq.pvalue, rq.paction, rq.status, rq.responseText);
      }
    }
  }
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_USETPARAM&uid="+rq.uid+"&pname="+rq.pname+"&pvalue="+rq.pvalue;  if (rq.paction) urlsend += '&taction='+rq.paction;
  rq.open("GET", urlsend, true);
  rq.send(null);
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
  fcalChangeUPref(-1,'WGCAL_U_TOOLSSTATE', s, null, fcalChangeUPrefDbg); // fcalChangeUPrefDbg);
}


                                                                                                                   

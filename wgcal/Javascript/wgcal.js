var debug = true;
function tdebug(tt) {
  if (!debug) return;

  if (!document.getElementById('debug')) {
    var deb = document.createElement('div');
    deb.setAttribute('id', 'debug');
    deb.style.position = 'absolute';
    deb.style.display = 'block';
    deb.style.backgroundColor = 'red';
    document.getElementById('root').appendChild(deb);
  }  else {
    var deb = document.getElementById('debug');
  }
  deb.style.left = '40px';
  deb.style.top = '40px';
  deb.innerHTML = tt;
}


function faxSendForm(e) {
  var param = '';
//   var tparam = '';
  var add = false;
  for (var ie=0; ie<e.elements.length; ie++) {
    add=false;
    if (e.elements[ie].name!='') {
      if (e.elements[ie].nodeName=='INPUT' && (e.elements[ie].type=='checkbox' || e.elements[ie].type=='radio')) {
	add = e.elements[ie].checked;
      } else {
	add = true;
      }
      if (add) {
	param += (param!=''?'&':'') + e.elements[ie].name+'='+escape(e.elements[ie].value);
//  	tparam += e.elements[ie].name+'='+escape(e.elements[ie].value)+'\n';
     }
    }
  }
  var rq;
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else rq = new ActiveXObject("Microsoft.XMLHTTP");
  rq.open("POST", e.action, false);
  rq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  rq.send(param);
//   alert('param=['+tparam+']\nrep=[\n'+rq.responseText+'\n]');
}



function eltId(eltid) {
  if (document.getElementById(eltid)) return document.getElementById(eltid);
  return false;
}

function fcalSetOpacity(o, value) {
  if (isIE) o.style.filter = 'alpha(opacity=' + value + ')';
  else o.style.opacity = value/100;
}


function  fcalGetJSDoc(ev, id) {
  var urlsend = UrlRoot+"app=WGCAL&action=WGCAL_DOCGETVALUES&id="+id;
  var res;
  res = fcalSendRequest(urlsend, false, false);
  if (res.status!=200) return false;
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

  var fw = frameWidth;
  var fh = frameHeight;
 
  var ow = parseInt(xm) + parseInt(delta);
  var oh = parseInt(ym) + parseInt(delta);

  var wlimit = false;
 
  if (ow+evWidth+30<fw) eid.style.left = ow+'px';
  else {
    ow = parseInt(fw) - (parseInt(evWidth) + 30);
    eid.style.left = ow+'px';
    wlimit = true;
  }
  
  if (oh+evHeight+30<fh) eid.style.top  = oh+'px';
  else {
    if (!wlimit) {
      oh = parseInt(fh) - (parseInt(evHeight) + 30);
    } else {
      oh = oh - (parseInt(evHeight) + 30);
    }
    eid.style.top = oh+'px';
  }
 
  
//   tdebug('Frame[w:'+fw+':h:'+fh+'] Card:[w:'+evWidth+':h:'+evHeight+'] X:'+eid.style.left+' Y:'+eid.style.top);


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
  usetparam(-1, "WGCAL_U_DAYSVIEWED", ndays, 'wgcal_calendar', UrlRoot+'app=WGCAL&action=WGCAL_CALENDAR');
}
function setTextView(sh) {
  usetparam(-1, "WGCAL_U_VIEW", "text", '', '');
  p = '';
  v = '';
  if (sh>0) {
    p = "WGCAL_U_CALCURDATE";
    v = sh;
  }
  usetparam(-1, p, v, 'wgcal_calendar', UrlRoot+'app=WGCAL&action=WGCAL_TEXTMONTH');
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
  var urlsend = UrlRoot+"app=WGCAL&action=WGCAL_USETPARAM&uid="+uid+"&pname="+pname+"&pvalue="+escape(pvalue);
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
  alert(' uid=['+uid+'] name=['+name+'] value=['+value+'] updatetarget=['+updatetarget+'] updateaction=['+updateaction+'] ');
  fset = document.getElementById('usetparam');
  taction = document.getElementById('taction');
  if (name!='') {
    document.getElementById('uid').value = uid;
    document.getElementById('pname').value = name;
    document.getElementById('pvalue').value = value;

    if (updatetarget=='') {
      updatetarget = 'wgcal_hidden';
      updateaction = 'WGCAL_HIDDEN';
    }

//   alert ('target='+updatetarget+' action = '+updateaction);

    taction.value = updateaction;
    fset.target = updatetarget;
    
    fset.submit();
  }
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




var isNetscape = navigator.appName=="Netscape";
// auxilarry window to select choice
var wichoose= false;

var colorPick = new ColorPicker();
initDHTMLAPI();

function sendmodifydoc(event,docid, attrid, sorm, index) {

  if (! index) index='';

  f =document.modifydoc;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=FDL&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&index='+index+'&wname='+window.name;

  
  if (index) attrid+=index;

  var xy= getAnchorWindowPosition(attrid);

  if (isNaN(window.screenX)){
    xy.y+=15; // add supposed decoration height
    // add body left width for IE sometimes ...
    if (parent.ffolder)  xy.x += parent.ffolder.document.body.clientWidth;
    
  }


  wichoose = window.open('', 'wchoose', 'scrollbars=yes,resizable=yes,height=30,width=290,left='+xy.x+',top='+xy.y);
  wichoose.focus();

  wichoose.moveTo(xy.x, xy.y+10);
  f.target='wchoose';


  
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


  var w, newsize;

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) { 
         if (elements[i].className == 'autoresize') {
	   w=getObjectWidth(elements[i].parentNode);
	   if (w > 45) {
	     newsize = (w - 45) / 9;
	     if (elements[i].type == 'text')
	       elements[i].size=newsize;
	     if (elements[i].type == 'textarea')
	       elements[i].cols=newsize;
	   }
	 }
       }
  }
}

// close auxillary window if open
function closechoose() {

    if (wichoose) wichoose.close();
}

function canmodify() {
    var attrNid=[attrnid];
    var err='';

    for (var i=0; i< attrNid.length; i++) {	
	if (document.getElementById(attrNid[i])) {
	  if (document.getElementById(attrNid[i]).value == '') {
	    ta = document.getElementsByName('_'+attrNid[i]+'[]');
	    if (ta.length == 0)	err += attrNid[i]+'\n';
	    for (var j=0; j< ta.length; j++) {
	      if (ta[j].value == '') err += attrNid[i]+'/'+(j+1)+'\n';
	    }
	  }
        }
    }
    if (err != '') {
	    alert(err+'[TEXT:some needed attributes are empty]');
	    return false;
    }
    return true;
}

// to define which attributes must be disabled
var tain= new Array();
var taout= new Array();
[BLOCK RATTR]
tain[[jska]]=[jstain];
taout[[jska]]=[jstaout];
[ENDBLOCK RATTR]

function disableReadAttribute() {
    
  var ndis = true;
  var i;
  var vin;
  var lin;
  for (var c=0; c< tain.length; c++) {
    ndis = true;
    for (var i=0; i< tain[c].length; i++) {
      if (document.getElementById(tain[c][i])) {
	vin=document.getElementById(tain[c][i]).value;
	if ((vin == '') || (vin == ' ')) ndis = false;
      }
    }
    for (var i=0; i< taout[c].length; i++) {
      if (document.getElementById(taout[c][i])) {
	if (document.getElementById(taout[c][i]).type != 'hidden') {
	  document.getElementById(taout[c][i]).disabled=ndis;
	  document.getElementById(taout[c][i]).style.backgroundColor=(ndis)?'[CORE_BGCOLORALTERN]':'';		
	}
      } else {
	// search in arrays
	lin = document.getElementsByName('_'+taout[c][i]+'[]');
	for (var j=0; j< lin.length; j++) {
	  if (lin[j].type != 'hidden') {
	    lin[j].disabled=ndis;
	    lin[j].style.backgroundColor=(ndis)?'[CORE_BGCOLORALTERN]':'';		
	  }
		
	}
	      
      }
    }
  }
}

function editOnLoad() {
    resizeInputFields();
    disableReadAttribute();
}


function clearInputs(tinput, idx) {
  var iinput;
  for (var i=0; i< tinput.length; i++) {
    iinput=tinput[i]+idx;
    if (document.getElementById(iinput)) {
      document.getElementById(iinput).value=' ';
      document.getElementById(iinput).style.backgroundColor='[CORE_BGCOLORHIGH]';
    }    
  }
  disableReadAttribute();

}


function unselectInput(id) {
  var sel=document.getElementById(id);
  if (sel) {
    for (var i=0; i< sel.options.length; i++) {
      sel.options[i].selected=false;
    }
  }
  sel.options[sel.options.length-1].selected=true;
}
function autoUnlock(docid) {
  if (parseInt(docid) > 0) {
    if (! document.isSubmitted) {
      var fhidden = window.open('','fhidden','');
      fhidden.document.location.href='[CORE_STANDURL]&app=FDL&action=UNLOCKFILE&auto=Y&id='+docid;
    }
  }
}

function pleaseSave(event) {
  if (document.isChanged && (! document.isSubmitted) && (! document.isCancelled)) {
    if (confirm('[TEXT:Save changes ?]')) {
      var bsubmit= document.getElementById('iSubmit');

      var can=bsubmit.onclick.apply(null,[event]);

      if (can) {
	var fedit= document.getElementById('fedit');
	if (fedit.onsubmit) fedit.onsubmit();
	fedit.submit();
      
      } else {
	alert('[TEXT:Data cannot be saved]');
	return false;
      }
    }
  }
  
  return true;
  
}

// change for time attribute
function chtime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);
  var shh,smm,ihh,imm;
  if (t && hh && mm) {
    ihh=parseInt(hh.value)%24;
    if (isNaN(ihh)) ihh=0;
    if (ihh < 10) shh='0'+ihh.toString();
    else shh=ihh.toString();
    hh.value=shh;

    imm=parseInt(mm.value)%60;
    if (isNaN(imm)) imm=0;
    if (imm < 10) smm='0'+imm.toString();
    else smm=imm.toString();
    mm.value=smm;

    t.value=shh+':'+smm;
  }
}

// change for time attribute
function clearTime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);

  if (t && hh && mm) {
   
    hh.value='';hh.style.backgroundColor='[CORE_BGCOLORHIGH]';
    mm.value='';mm.style.backgroundColor='[CORE_BGCOLORHIGH]';

    t.value=' ';
  }
}
function addinlist(sel,value) {

  if (isNetscape) pos=null;
  else pos=sel.options.length+1;
  sel.add(new Option(value, value, false, true),pos);
}

// replace s1 by s2 in node n
function  nodereplacestr(n,s1,s2) {
  
  var kids=n.childNodes;
  var ka;
  var avalue;
  var rs1;
  var attnames = new Array('onclick','href','onmousedown','id','name');
  // for regexp
    rs1 = s1.replace('[','\\[');
  rs1 = rs1.replace(']','\\]');
  
  for (var i=0; i< kids.length; i++) {     
    if (kids[i].nodeType==3) { 
      // Node.TEXT_NODE
	
	if (kids[i].data.search(rs1) != -1) {
	  kids[i].data = kids[i].data.replace(s1,s2);
	}
    } else if (kids[i].nodeType==1) { 
      // Node.ELEMENT_NODE
	
	// replace  attributes defined in attnames array
	  for (iatt in attnames) {
	    
	    attr = kids[i].getAttributeNode(attnames[iatt]);
	    if ((attr != null) && (attr.value != null) && (attr.value != 'null'))  {
	      
	      
	      if (attr.value.search(rs1) != -1) {
		
		avalue=attr.value.replace(s1,s2);

		if ((!isNetscape) && ((attr.name == 'onclick') || (attr.name == 'onmousedown'))) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
		else 
		  attr.value=avalue;
	      }
	    }
	  }
      nodereplacestr(kids[i],s1,s2);
    } 
  }
}
 

//-------------------------------------------------------------
// select tr (row table) 
var seltr=false; 
function addtr(trid, tbodyid) {
  
  var ntr;
  with (document.getElementById(trid)) {
    // need to change display before because IE doesn't want after clonage
    style.display='';

    ntr = cloneNode(true);
    style.display='none';
  }
  
  ntr.id = '';
  ntable = document.getElementById(tbodyid);
  ntable.appendChild(ntr);

  nodereplacestr(ntr,'-1',ntable.childNodes.length);
  resizeInputFields(); // need to revaluate input width
 

  if (seltr)  {
    seltr.parentNode.insertBefore(ntr,seltr);
  } else {
    var ltr = ntable.getElementsByTagName('tr');
    if (ltr.length > 1) ltr[ltr.length-2].parentNode.insertBefore(ntr,ltr[ltr.length-2]);
  }
  
}

// use to delete an article
function deltr(tr) {


  tr.parentNode.removeChild(tr);
  
  return;
  
}

function resetInputsByName(name) {
  if (! isNetscape) return;
  var la=document.getElementsByName(name);
  if (la) {
    for (var i=0; i< la.length; i++) { 
	    la[i].parentNode.insertBefore(la[i],la[i].nextSibling);      
	  }
  }
}

function resetTrInputs(tr) {
  if (! isNetscape) return;
  var tin = tr.getElementsByTagName('input');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }

  // add select input also
  tin = tr.getElementsByTagName('select');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }

}
// up tr order 
function uptr(trnode) {

  var pnode = trnode.previousSibling;
  var textnode=false;

  while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??

  if (pnode)  {
    trnode.parentNode.insertBefore(trnode,pnode);
    
  }  else {
    trnode.parentNode.appendChild(trnode); // latest (cyclic)
  }
  resetTrInputs(trnode);
  return;  
}

// down tr order 
function downtr(trnode) {

  var nnode = trnode.nextSibling;

  while (nnode && (nnode.nodeType != 1)) nnode = nnode.nextSibling; // case TEXT attribute in mozilla between TR ??

  if (nnode ) {
      nnnode = nnode.nextSibling; 
      while (nnnode && (nnnode.nodeType != 1)) nnnode = nnnode.nextSibling; // case TEXT attribute in mozilla between TR ??

      if (nnnode) 
         trnode.parentNode.insertBefore(trnode,nnnode);
      else 
         trnode.parentNode.appendChild(trnode); // latest
  } else {
      trnode.parentNode.insertBefore(trnode,trnode.parentNode.firstChild); // latest (cyclic)
  }


  resetTrInputs(trnode);
  return;  
}

// use to delete an article
function delseltr() {


  if (seltr) {
    seltr.parentNode.removeChild(seltr);  
    seltr=false;
    visibilityinsert('insertup','hidden');
  }
  return;
  
}

function visibilityinsert(n,d) {
  var ti = document.getElementsByName(n);
  for (var i=0; i< ti.length; i++) { 
    ti[i].style.visibility=d;
  }
}

function selecttr(tr) {

  if (seltr) {
    seltr.style.backgroundColor='';
    
  } else {
    
    visibilityinsert('insertup','visible');
  }

  seltr=tr;

  seltr.style.backgroundColor='lightgrey';


  return;  
}

//unselect selected
function unseltr() {

  if (seltr) {
    seltr.style.backgroundColor='';
    
    visibilityinsert('insertup','hidden');
  }

  seltr=false;

  return;  
}

function movetr(tr) {

  var trnode= seltr;
  var pnode = tr;
  if (seltr) {  

    while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??
    if (pnode)  {
      trnode.parentNode.insertBefore(trnode,pnode);
    
    }  else {
      trnode.parentNode.appendChild(trnode); // latest (cyclic)
    }
    
    resetTrInputs(trnode);
  }
  return;  
}

//-----------------------------------------

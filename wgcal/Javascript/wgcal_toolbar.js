var cp = new ColorPicker();

var curRessource = -1;

function pickColor(color) {
  if (curRessource==-1) return;
  idx = getRessourcePos(curRessource);
  ressourceList[idx][1] = color;
  document.getElementById('cp'+curRessource).style.background = color;
  ressourcesChange = 1;
}
   
function showColorPicker(event, id, idress) {
  curRessource = idress;
  cp.show(id);
}

var picker = null;

window.onunload = killwins;
function killwins() {
  if (picker != null) picker.close();
  if (toolsVisibilityChange==1) {
    WGCalSaveToolsVisibility();
  }
  if (ressourcesChange == 1) {
    ok = confirm('[TEXT: ressource list changed, save it ?]');
    if (ok) saveRessources();
  }
}

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

var isNetscape = navigator.appName=="Netscape";


// ----------------------------------------------
var ressourceList = new Array();
var ressourcesChange = 0;

function getRessourcePos(rid) {
  var idx = -1;
  for (i=0; i<ressourceList.length && idx==-1; i++) {
    if (ressourceList[i][0] == rid) idx = i;
  }
  return idx;
}
 
function addRessource(rid, rtitle, ricon) {
  idx = getRessourcePos(rid);
  if (idx!=-1) return;
  InsertRessource( rtitle, rid, ricon, '#00FFFF', 'WGCRessDefault', 0 );
  ressourcesChange = 1;
}

function storeRessource(id, color, display, icon, descr, style) {
  idx = getRessourcePos(id);
  if (idx==-1)  {
    idx = ressourceList.length;
    ressourceList[idx] = new Array();
  }
  ressourceList[idx][0] = id;
  ressourceList[idx][1] = color;
  ressourceList[idx][2] = display;
  ressourceList[idx][3] = icon;
  ressourceList[idx][4] = descr;
  ressourceList[idx][5] = style;
}

function deleteRessource(rid) {
  var eltRess;
  idx = getRessourcePos(rid);
  if (idx!=-1) ressourceList[idx][0] = -1;
  eltRess = document.getElementById('tr'+rid);
  if (!eltRess) return;
  eltRess.parentNode.deleteRow(eltRess.sectionRowIndex);
}

function showAllRessource() {
  var tt = '';
  for (i=0; i<ressourceList.length;i++) {
    tt += printRessource(i);
  }
  alert(tt);
}
  
function printRessource(i) {
  var m = "";
  if (ressourceList[i][0] != -1 ) {
    m = 'Ressource #'+i+' == ';
    m += ressourceList[i][0]+":"+ressourceList[i][1]+":"+ressourceList[i][2]+":"+ressourceList[i][3]+":"+ressourceList[i][4]+":"+ressourceList[i][5]+"\n";
  }
  return m;
}

function InsertRessource( rdescr, rid, ricon, rcolor, rstyle, rstate ) {
  var nTr;
  var tab;

  idx = getRessourcePos(rid);
  if (idx!=-1) {
    deleteRessource(rid);
  }
  tab = document.getElementById('tabress');
  if (!tab) {
    alert('tabress not defined');
    return;
  }
  with (document.getElementById('trsample')) {
    style.display = '';
    nTr = cloneNode(true);
    style.display = 'none';
  }
  nTr.id = 'tr'+rid;
  mynodereplacestr(nTr, '%RID%', rid);
  mynodereplacestr(nTr, '%RICON%', ricon);
  mynodereplacestr(nTr, '%RDESCR%', rdescr);
  nTr.style.display = '';
  tab.appendChild(nTr);
  storeRessource(rid, rcolor, rstate, ricon, rdescr, rstyle);
  document.getElementById(rid).className = rstyle;
  document.getElementById('cp'+rid).style.background = rcolor;
}

function setRessourceState(rid, setStyle, unsetStyle) {
  if (rid==-1) {
    alert('[TEXT: invalid ressource id]');
    return;
  }
  ressourcesChange = 1;
  idx = getRessourcePos(rid);
  if (ressourceList[idx][2] == 1) {
    ressourceList[idx][2] = 0;
    rstyle = unsetStyle;
  } else {
    ressourceList[idx][2] = 1;
    rstyle = setStyle;
  }
  document.getElementById(rid).className = rstyle;
  return;
}
   

function saveRessources() {
  var rlist= "";
  for (i=0; i<ressourceList.length;i++) {
    if (ressourceList[i][0] != -1 ) rlist += ressourceList[i][0]+"%"+ressourceList[i][2]+"%"+ressourceList[i][1]+"|";
  }
  document.getElementById('savelist').value = rlist;
  document.getElementById('f_saveress').submit();
  ressourcesChange = 0;
  return;
}

function updateCalendar() {
  if (ressourcesChange == 1) {
    ok = confirm('[TEXT: ressource list changed, save it ?]');
    if (ok) saveRessources();
  }
  document.getElementById('updatecal').submit();
}

// --------------------------------------------------------
 
var toolsVisibilityChange = 0;
var datePickerVisible = 1;
var resslistVisible = 1;
                      
function WGCalSetVisibility(state, eid) {
  eld = document.getElementById(eid); 
  if (!eld) return;
  if (state==0) eld.style.display = 'none';
  else eld.style.display = '';
}
function WGCalChangeVisibility(state, eid) {
  toolsVisibilityChange = 1;
  if (state==0) state=1;
  else state=0;
  WGCalSetVisibility(state,eid);
  return state;
}
function WGCalSaveToolsVisibility() {
  toolsVisibilityChange = 0;
  document.getElementById('toolsvis').value = "DATENAV%"+datePickerVisible+"|RESSLIST%"+resslistVisible;
  document.getElementById('ftoolsvis').submit();
}
                                                                                                                   
 

   

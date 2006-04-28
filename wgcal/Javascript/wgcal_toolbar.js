var cp = new ColorPicker('window');

var curRessource = -1;

function pickColor(color) {
  if (curRessource!=-1) {
    idx = getRessourcePos(curRessource);
    if (idx==-1) return;
    ressourceList[idx][1] = color;
    document.getElementById('cp'+curRessource).style.background = color;
    saveRessources();
  }
}
   
function showColorPicker(event, idress) {
  curRessource = idress;
  cp.show('cp'+idress);
}



var picker = null;

window.onunload = killwins;
function killwins() {
  if (picker != null) picker.close();
}



// ----------------------------------------------
var ressourceList = new Array();
var ressListChg = true;
var rsList = ""; // Idem ressourceList, but in "xxx|yyy|aaa" form (just ids for selected ressources)

function getRessourcePos(rid) {
  var idx = -1;
  for (i=0; i<ressourceList.length && idx==-1; i++) {
    if (ressourceList[i][0] == rid) idx = i;
  }
  return idx;
}
 
function addRessource(rid, rtitle, ricon, rstate, rcolor, rselect, ro) {
  idx = getRessourcePos(rid);
  if (idx!=-1) return;
  InsertRessource( rtitle, rid, ricon, '#00FFFF', 'WGCRessDefault', 0, ro);
  tdiv['resspopup'][rid]=[1,1,1,1,1,1,1,1,1,1];
  saveRessources();
}

function storeRessource(id, color, display, icon, descr, style, romode, adhave, adselected) {
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
  ressourceList[idx][6] = romode;
  ressourceList[idx][7] = adhave;
  ressourceList[idx][8] = adselected;
 
  if (ressourceList[idx][2] == 1) rsList += ressourceList[idx][0]+'|';
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

function InsertRessource( rdescr, rid, ricon, rcolor, rstyle, rstate, romode, adhave, adselected ) {
  var nTr;
  var tab;

  idx = getRessourcePos(rid);
  if (idx!=-1) {
    removeRessource(rid);
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
  document.getElementById('cp'+rid).style.background = rcolor;
  document.getElementById(rid).className = rstyle;
  if (romode) document.getElementById('imro'+rid).style.display = '';
  else document.getElementById('imro'+rid).style.display = 'none';
  storeRessource(rid, rcolor, rstate, ricon, rdescr, rstyle, romode, adhave, adselected );

  // delegation
  if (adhave) document.getElementById('agd'+rid).style.display='block';
  else document.getElementById('agd'+rid).style.display='none';
  if (adselected) document.getElementById('agd'+rid).checked = true;
  else document.getElementById('agd'+rid).checked = false;
}

var CRessId = -1;
var CRessText = '';

function vuvRessource() {
  if (CRessId==-1) {
    alert('[TEXT: invalid ressource id]');
    return;
  }
  idx = getRessourcePos(CRessId);
  if (idx==-1) return;
  if (ressourceList[idx][2] == 1) {
    ressourceList[idx][2] = 0;
    rstyle = 'WGCRessDefault';
  } else {
    ressourceList[idx][2] = 1;
    rstyle = 'WGCRessSelected';
  }
  ressListChg = true;
  document.getElementById(CRessId).className = rstyle;
  saveRessources();
  return;
}

function showHideAllRess(show) {
  var ir;
  for (ir=0; ir<ressourceList.length; ir++) {
    if (ressourceList[ir][0]>1) {
      if (show==-2) {
	if (ressourceList[ir][2] == 1) {
	  document.getElementById(ressourceList[ir][0]).className = 'WGCRessDefault';
	  ressourceList[ir][2] = 0;
	} else {
	  document.getElementById(ressourceList[ir][0]).className = 'WGCRessSelected';
	  ressourceList[ir][2] = 1;
	}
      } else if (show==-1) {
	document.getElementById(ressourceList[ir][0]).className = 'WGCRessDefault';
	ressourceList[ir][2] = 0;
      } else if (show==0) {
	document.getElementById(ressourceList[ir][0]).className = 'WGCRessSelected';
	ressourceList[ir][2] = 1;
      } else if (show>0) {
	if (ressourceList[ir][0] == show) {
	  document.getElementById(ressourceList[ir][0]).className = 'WGCRessSelected';
	  ressourceList[ir][2] = 1;
	} else {
	  document.getElementById(ressourceList[ir][0]).className = 'WGCRessDefault';
	  ressourceList[ir][2] = 0;
	}
      }
    }
  }
  ressListChg = true;
  saveRessources();
  return;
}

function removeRessource() {
  var eltRess;
  if (CRessId==-1) {
    alert('[TEXT: invalid ressource id]');
    return;
  }
  var idx = getRessourcePos(CRessId);
  if (idx!=-1) {
    ressourceList[idx][0] = -1;
    if (ressourceList[idx][2] == 1) ressListChg = true;
    eltRess = document.getElementById('tr'+CRessId);
    if (!eltRess) return;
    eltRess.parentNode.deleteRow(eltRess.sectionRowIndex);
    saveRessources();
  }
}

function saveRessources() {
  var rlist= "";
  rsList = "";
  for (i=0; i<ressourceList.length;i++) {
    if (ressourceList[i][0] != -1 ) {
      rlist += ressourceList[i][0]+"%"+ressourceList[i][2]+"%"+ressourceList[i][1]+"|";
      if (ressourceList[i][2] == 1) rsList += ressourceList[i][0]+'|';
    }
  }
  if (ressListChg) {
    usetparam(-1, "WGCAL_U_RESSDISPLAYED", rlist, 'wgcal_calendar', 'WGCAL_CALENDAR');
  } else {
    usetparam(-1, "WGCAL_U_RESSDISPLAYED", rlist, 'wgcal_hidden', 'WGCAL_HIDDEN');
  }
  ressListChg = false;
  return;
}

function SaveFrameWidth() {
  var w=getFrameWidth(window);
  usetparam(-1, "WGCAL_U_TOOLBARSZ", w, 'wgcal_hidden', 'WGCAL_HIDDEN');
  return;
}

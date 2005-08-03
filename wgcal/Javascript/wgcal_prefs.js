// prefered contacts management

var PrefContactsList = new Array();

function getContactPos(rid) {
  var idx = -1;
  for (i=0; i<PrefContactsList.length && idx==-1; i++) {
    if (PrefContactsList[i][0] == rid) idx = i;
  }
  return idx;
}
 
function addRessource(rid, rtitle, ricon, rstate) {
  idx = getContactPos(rid);
  if (idx!=-1) return;
  InsertPrefContact( -1, rtitle, rid, ricon, true);
}

function InsertPrefContact(uid, rdescr, rid, ricon, saveIt) {
  var nTr;
  var tab;

  idx = getContactPos(rid);
  if (idx!=-1) deleteContact(rid);
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
  idx = PrefContactsList.length;
  PrefContactsList[idx] = new Array();
  PrefContactsList[idx][0] = rid;
  PrefContactsList[idx][1] = ricon;
  PrefContactsList[idx][2] = rdescr;
  if (saveIt) saveContacts(uid);
}

function deleteContact(uid, rid) {
  var eltRess;
  idx = getContactPos(rid);
  if (idx!=-1) PrefContactsList[idx][0] = -1;
  eltRess = document.getElementById('tr'+rid);
  if (!eltRess) return;
  eltRess.parentNode.deleteRow(eltRess.sectionRowIndex);
  saveContacts(uid);
}

function saveContacts(uid) {
  var rlist= "";
  for (i=0; i<PrefContactsList.length;i++) {
    if (PrefContactsList[i][0] != -1 ) 
      rlist += PrefContactsList[i][0]+"|";
  }
  usetparam(uid, "WGCAL_U_PREFRESSOURCES", rlist, 'wgcal_hidden', 'WGCAL_HIDDEN');
  return;
}


function UseContactInRv(uid) {
  ckb = document.getElementById('usecontact');
  ckb.checked = (ckb.checked ? "" : "checked" );
  if (ckb.checked) usetparam(uid, "WGCAL_U_USEPREFRESSOURCES", 1, 'wgcal_hidden', 'WGCAL_HIDDEN');
  else usetparam(uid, "WGCAL_U_USEPREFRESSOURCES", 0, 'wgcal_hidden', 'WGCAL_HIDDEN');
}

function SetChkPref(uid, opt, prm, target, action) {
  ckb = document.getElementById(opt);
  if (!ckb) return;
  ckb.checked = (ckb.checked ? "" : "checked" );
    if (ckb.checked) usetparam(uid, prm, 1, target, action);
  else usetparam(uid, prm, 0, target, action);
}

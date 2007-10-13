function forum_openclose(event, eid, forceopen) {
  if (eid==entry_edit) return;
  if (!document.getElementById('fc_'+eid)) return;
  var forum = document.getElementById('fc_'+eid);
  var fimg  = document.getElementById('fi_'+eid);
  if (forum.style.display!='none' && !forceopen) {
    forum.style.display = 'none';
    fimg.src =  '[IMGF:forum_close.gif:0,0,0|COLOR_BLACK]';
  } else {
    forum.style.display = 'block';
    fimg.src =  '[IMGF:forum_open.gif:0,0,0|COLOR_BLACK]';
  }
}


var entry_edit = -1;
var entry_hide = -1;
var entry_change = false;
function forum_edit(event, docid, ref, link, eid) {
  // link = -1 root entry 
  // eid  = -1 new entry

  if (!document.getElementById('forum_editform')) return;

  forum_openclose(event, ref, true);
  forum_cancelEdit(event);

  var text = '';
  if (eid>0 && document.getElementById('ft_'+eid)) {
    document.getElementById('ft_'+eid).style.display = 'none';
    text = document.getElementById('ft_'+eid).innerHTML;
    entry_hide = eid;
  }
  document.getElementById('foredit_eid').value = eid;
  document.getElementById('foredit_link').value = link;
  document.getElementById('foredit_text').value = text;

  var fedit = document.getElementById('forum_editform');
  GetXY(event);

  var mark = document.getElementById('fm_'+ref);
  mark.appendChild(fedit);
//   fedit.style.visibility = 'visible';
  fedit.style.display = 'block';
  document.getElementById('foredit_text').focus();

  entry_edit = ref;
  stopPropagation(event);
} 

function forum_sendmail(event, addr, docid, eid) {

  stopPropagation(event);
}
function forum_opacity(oid, value) {
  if (!document.getElementById(oid)) return;
  var o = document.getElementById(oid);
  if (isIE) o.style.filter = 'alpha(opacity=' + value + ')';
  else o.style.opacity = value/100;
}


function forum_change(event) {
  if (entry_edit!=-1) {
    entry_change=true;
    //     addEvent(window,"unload",forum_cancelEdit);
    window.onbeforeunload = forum_cancelEdit;
  }
  return;
}

function forum_cancelEdit(event) {
  if (entry_edit!=-1 && entry_change) {
    var ok = confirm('[TEXT:save forum edition]');
    if (ok) forum_saveEdit(event);
  }
  forum_clean();
  return true;
}

function forum_saveEdit(event) {

  var corestandurl=window.location.pathname+'?sole=Y&';
  enableSynchro();

  var docid = document.getElementById('foredit_docid').value;
  var eid   = document.getElementById('foredit_eid').value;
  var link  = document.getElementById('foredit_link').value;
  var text  = document.getElementById('foredit_text').value;

  var url = corestandurl+'app=FDL&action=FDL_FORUMADDENTRY&docid='+docid+'&eid='+eid+'&lid='+link+'&text='+text;
  
  requestUrlSend(document.getElementById('f_X'),url);
  disableSynchro();
  
  forum_clean();
  return;
}

function forum_clean(event) {
  if (entry_hide>0 && document.getElementById('ft_'+entry_hide)) {
    document.getElementById('ft_'+entry_hide).style.display = 'block';
  }
  entry_hide = -1;
  entry_edit = -1;
  entry_change = false;
  var fedit = document.getElementById('forum_editform');
  fedit.style.display = 'none';
  return;
}

function forum_openclose(event, eid) {
  if (!document.getElementById('fc_'+eid)) return;
  var forum = document.getElementById('fc_'+eid);
  var fimg  = document.getElementById('fi_'+eid);
  if (forum.style.display!='none') {
    forum.style.display = 'none';
    fimg.src =  '[IMGF:forum_close.gif:0,0,0|COLOR_BLACK]';
  } else {
    forum.style.display = 'block';
    fimg.src =  '[IMGF:forum_open.gif:0,0,0|COLOR_BLACK]';
  }
}

function forum_edit(event, docid, link, eid) {
  // link = -1 root entry 
  // eid  = -1 new entry

  if (!document.getElementById('forum_editform')) return;
  var fedit = document.getElementById('forum_editform');
  fedit.style.display = 'block';
  fedit.style.visibility = 'visible';

  stopPropagation(event);
}
function forum_sendmail(event, addr, docid, eid) {

  stopPropagation(event);
}


// use when submit to avoid first unused item
function deletenew() {
  resetInputs('newcond');
  var na=document.getElementById('newcond');
  if (na) na.parentNode.removeChild(na); 
  na=document.getElementById('newstate');
  if (na) na.parentNode.removeChild(na);
  
  
}
  

function trackCR(event) {
  var intKeyCode;

  if (!event) event=window.event;
  intKeyCode=event.keyCode;
  if (intKeyCode == 13) return true;

  return false;
}
function sendsearch(faction,rtarget) {
  var fedit = document.fedit;
  resetInputs('newcond');
  
  with (document.modifydoc) {
    var editAction=action;
    var editTarget=target;

    enableall();  
    var na=document.getElementById('newcond');
    if (na) {
      disabledInput(na,true);        
      var nt=document.getElementById('newstate');
      if (nt)   disabledInput(nt,true);
    }
    if (!rtarget) rtarget='fvfolder';
    target=rtarget;
    action=faction;
    submit();
    target=editTarget;
    action=editAction;

    
    if (na) {
      disabledInput(na,false);            
       if (nt) disabledInput(nt,false);
    }
    
  }
}

function setKey(event,th) {
  var pnode;

  pnode=th.previousSibling;
  while (pnode && ((pnode.nodeType != 1) || (pnode.name != '_se_keys[]'))) pnode = pnode.previousSibling;

  pnode.value = th.options[th.selectedIndex].value;

  
}

function getNextElement(th) {
  var pnode;
  pnode=th.nextSibling;
  while (pnode && (pnode.nodeType != 1)) pnode = pnode.nextSibling;
  return pnode;
  
}

function filterfunc(th) {
  var p=th.parentNode;
  var opt=th.options[th.selectedIndex];
  var atype=opt.getAttribute('atype');
  var ctypes,i;
  var pnode,so=false;

  // search brother select input
  pnode=p.nextSibling;
  while (pnode && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;

  pnode.style.border='red 1px solid';
  for (i=0;i<pnode.childNodes.length;i++) {
    if (pnode.childNodes[i].tagName=='SELECT') {
      so=pnode.childNodes[i];
    }
  }

  // display only matches
  for (i=0;i<so.options.length;i++) {
    opt=so.options[i];
    ctype=opt.getAttribute('ctype');
    if ((ctype=='') || (ctype.indexOf(atype)>=0)) {
      opt.style.display='';
      opt.disabled=false;
    } else {
      opt.style.display='none';
      opt.selected=false;
      opt.disabled=true;
    }
  }
  
}

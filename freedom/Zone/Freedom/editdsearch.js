
// use when submit to avoid first unused item
function deletenew() {
  resetInputs('newcond');
  var na=document.getElementById('newcond');
  if (na) na.parentNode.removeChild(na); 
  na=document.getElementById('newstate');
  if (na) na.parentNode.removeChild(na);
  
  
}
  


function sendsearch(faction) {
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
    target='flist';
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


  pnode=th.previousSibling;
  while (pnode && ((pnode.nodeType != 1) || (pnode.name != '_se_keys[]'))) pnode = pnode.previousSibling;

  pnode.value = th.options[th.selectedIndex].value;

  
}

function getNextElement(th) {
  pnode=th.nextSibling;
  while (pnode && (pnode.nodeType != 1)) pnode = pnode.nextSibling;
  return pnode;
  
}

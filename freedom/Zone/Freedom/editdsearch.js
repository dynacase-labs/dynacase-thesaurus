
// use when submit to avoid first unused item
function deletenew() {
  resetInputs('newcond');
  var na=document.getElementById('newcond');
  na.parentNode.removeChild(na);
  
  
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

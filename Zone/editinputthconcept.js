function displayconcepttree(event,aid) {
  var corestandurl=window.location.pathname+'?sole=Y';
  var cible=document.getElementById('tree_'+aid);
  if (cible) {
   
  var idtree=document.getElementById(aid).getAttribute('thesaurus');
  var filter=document.getElementById('label_'+aid).value;
  cible.style.visibility='hidden';
  var url=corestandurl+'&app=THESAURUS&action=INPUTTREE&id='+idtree+'&filter='+filter+'&aid='+aid;
  enableSynchro();
  var ret=requestUrlSend(cible,url);
  var h=0;
  disableSynchro();
  cible.style.visibility='visible';
  cible.style.display='';
  
  resizeme(event,cible.id);
    
  }
}
function selectth(th,thid,aid) {
  var filter=document.getElementById('label_'+aid);
  var treediv=document.getElementById('tree_'+aid);
  var thb=document.getElementById('it_'+aid);
  var realid=document.getElementById(aid);
  realid.value=thid;
  if (th.textContent)  filter.value=th.textContent;
  else filter.value=th.innerText;
  filter.disabled=true;;
  if (thb) thb.disabled=true;

  treediv.style.display='none';
}
function resizeme(event,divid) {
  var th=document.getElementById(divid);
  var h=0;
    for (var i=0; i < th.childNodes.length ; i++) {
      if (th.childNodes[i].nodeType == 1) {
	h=getObjectHeight(th.childNodes[i]);
	break;
      }
    }
    if (h > 150) h=150;
    if (h > 0) th.style.height=h+'px';
}

function clearconcept(event,aid) {
  var filter=document.getElementById('label_'+aid);
  var thb=document.getElementById('it_'+aid);
  var cid=document.getElementById(aid);
  var cible=document.getElementById('tree_'+aid);
  if (cible) cible.style.display='none';

  if (filter) {
    filter.value="";
    filter.disabled=false;
    filter.focus();
  }
  if (thb) thb.disabled=false;
  if (cid) cid.value="";
}

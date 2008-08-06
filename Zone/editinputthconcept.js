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
  disableSynchro();
  cible.style.visibility='visible';
  cible.style.display='';
  }
}
function selectth(th,thid,aid) {
  
  var filter=document.getElementById('label_'+aid);
  var realid=document.getElementById(aid);
  if (th.textContent)  filter.value=th.textContent;
  else filter.value=th.innerText;
  realid.value=thid;
  
}

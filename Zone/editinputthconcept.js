function displayconcepttree(event,aid,multi) {
  var corestandurl=window.location.pathname+'?sole=Y';
  var cible=document.getElementById('tree_'+aid);
  if (cible) {
   
  var idtree=document.getElementById(aid).getAttribute('thesaurus');
  var filter=document.getElementById('label_'+aid).value;
  cible.style.visibility='visible';
  cible.style.display='';clipboardWait(cible);
  var url=corestandurl+'&app=THESAURUS&action=INPUTTREE&id='+idtree+'&filter='+filter+'&aid='+aid;
  enableSynchro();
  if (multi) url = url + '&multi=yes';
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
  var thb=document.getElementById('it_'+aid);
  var realid=document.getElementById(aid);
  realid.value=thid;
  if (th.textContent)  filter.value=th.textContent;
  else filter.value=th.innerText;
  filter.disabled=true;;
  if (thb) thb.disabled=true;
  undisplaytree(aid);
  
}

function undisplaytree(aid) {
  var treediv=document.getElementById('tree_'+aid);
  treediv.style.display='none';
  
}

function selectmultith(th,thid,aid) {
  var filter=document.getElementById('label_'+aid);
  var treediv=document.getElementById('tree_'+aid);
  var opto=document.getElementById('thopt_'+aid);
  var thb=document.getElementById('it_'+aid);
  var realid=document.getElementById(aid);
  var valuetext;
  var selected=th.getAttribute("selected");
 
  if (selected == "1") {
    
    removeinlist(opto, thid);
    th.setAttribute("selected","0");
  } else {
    if (th.textContent)  valuetext=th.textContent;
    else valuetext=th.innerText;
    addinlist(opto,valuetext, thid,true);
    th.setAttribute("selected","1");
  }


  transfertDocIdInputs(opto,realid);
  inversecolor(th);

}
function inversecolor(o) {
  var bgcolor=getCssStyle(o,'backgroundColor');
  var po;

  if (bgcolor=='transparent') {
    po=o.parentNode;
    while (po && bgcolor=='transparent') {
      bgcolor=getCssStyle(po,'backgroundColor');
      po=po.parentNode;
    }
  }
  
  o.style.backgroundColor=getCssStyle(o,'color');
  o.style.color=bgcolor;
}
// select in tree values already set from aid input
function preselectMultiTree(treeid,aid) {
  var tree=document.getElementById(treeid);
  var as=tree.getElementsByTagName('a');
  var aids=document.getElementById(aid).value;

  var thid;
  for (var i=0;i<as.length;i++) {
    thid=as[i].getAttribute('thid');
    if (aids.indexOf(thid) >= 0) {
      if (as[i].getAttribute("selected")!="1") {
	inversecolor(as[i]);      
	as[i].setAttribute("selected","1");
      }
    } else if (as[i].getAttribute("selected")=="1") {
      inversecolor(as[i]);      
      as[i].setAttribute("selected","0");      
    }
  }
}

function resizeme(event,divid) {
  var th=document.getElementById(divid);
  var h=0,w;
    for (var i=0; i < th.childNodes.length ; i++) {
      if (th.childNodes[i].nodeType == 1) {
	h=getObjectHeight(th.childNodes[i]);

	break;
      }
    }
    if (h > 150) h=150;
    if (isIE) {
      h+=10;
      w=getObjectWidth(th); // force width to workaround scrollbar effect
      th.style.width=w+'px';
    }
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

function displayconcepttree(event,aid,multi) {
  var corestandurl=window.location.pathname+'?sole=Y';
  var cible=document.getElementById('tree_'+aid);
  var ix=document.getElementById('ix_'+aid);
  if (cible) {   
    var idtree=document.getElementById(aid).getAttribute('thesaurus');
    var filter=document.getElementById('label_'+aid).value;
    var itdelta=document.getElementById('it_'+aid);
    var oldv=itdelta.value;
    itdelta.setAttribute("oldvalue",itdelta.value);
    itdelta.value='O';
    cible.style.visibility='visible';
    cible.style.display='';
    clipboardWait(cible);

    if (ix) ix.disabled=false;
    setTimeout(function() {sendconcepttree(event,aid,multi);},10); // force event loop to view waiting effects         
  }
}

function sendconcepttree(event,aid,multi) {
  var corestandurl=window.location.pathname+'?sole=Y';
  var cible=document.getElementById('tree_'+aid);
  if (cible) {   
    var idtree=document.getElementById(aid).getAttribute('thesaurus');
    var filter=document.getElementById('label_'+aid).value;
    var itdelta=document.getElementById('it_'+aid);
   

    var url=corestandurl+'&app=THESAURUS&action=INPUTTREE&id='+idtree+'&filter='+filter+'&aid='+aid;
    enableSynchro();
    if (multi) url = url + '&multi=yes';
    var ret=requestUrlSend(cible,url); 
    disableSynchro();
    cible.style.visibility='visible';
    cible.style.display='';

    itdelta.value=itdelta.getAttribute('oldvalue');
    resizeme(event,cible.id);        
  }
}
function selectth(th,thid,aid) {
  var filter=document.getElementById('label_'+aid);
  var thb=document.getElementById('it_'+aid);
  var realid=document.getElementById(aid);
  var w=getObjectWidth(filter);
  realid.value=thid;


  if (th.textContent)  filter.value=th.textContent;
  else filter.value=th.innerText;
  if (isIE && w) filter.style.width=w+'px';
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
  var w=getObjectWidth(opto);
  if (selected == "1") {
    
    removeinlist(opto, thid);
    th.setAttribute("selected","0");
  } else {
    if (th.textContent)  valuetext=th.textContent;
    else valuetext=th.innerText;

    
    //    if (valuetext.length > 40) valuetext=valuetext.substr(0,40);

    addinlist(opto,valuetext, thid,true);
    opto.style.visibility='visible';
    if (w) opto.style.width=w+'px'; // force to not change width
    th.setAttribute("selected","1");
  }


  transfertDocIdInputs(opto,realid);
  inversecolor(th);

}
function inversecolor(o) {
  var bgcolor=getCssStyle(o,'backgroundColor');
  var po;
  if ((bgcolor=='transparent')||(bgcolor=='rgba(0, 0, 0, 0)')) {
    po=o.parentNode;
    while (po && ((bgcolor=='transparent')||(bgcolor=='rgba(0, 0, 0, 0)'))) {
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
  if (! tree) return;
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
  var otop=getCssStyle(th,'top');//getObjectTop(th);  
  if (h > 150) h=150;
  if (isIE) {
    h+=10;
    w=getObjectWidth(th); // force width to workaround scrollbar effect
    th.style.width=w+'px';
  }
  if (isSafari) {
    var xy=getAnchorPosition(divid);    
    th.style.top=xy.y;    
  }

  if (h > 0) th.style.height=h+'px';
  if (th.scrollHeight > th.clientHeight) th.style.paddingRight='10px';
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

function addmultiselectth(th,thid,aid) {;
  var opto=document.getElementById('thopt_'+aid);
  var valuetext;
  var selected=th.getAttribute("selected");
  
  if (selected == "1") {
    
    removethinlist(opto, thid);
    th.setAttribute("selected","0");
  } else {
    if (th.textContent)  valuetext=th.textContent;
    else valuetext=th.innerText;    

    addthinlist(opto,valuetext, thid,false);
    opto.style.visibility='visible';
    
    th.setAttribute("selected","1");
  }


  inversecolor(th);

}
function addselectth(th,thid,aid) {;
  var opto=document.getElementById('thopt_'+aid);
  var tree=document.getElementById('thtree'+aid);
  var valuetext;
  var selected=th.getAttribute("selected");
  opto.options.length=0;
  unselecttree(tree);
  if (selected == "1") {        
    th.setAttribute("selected","0");
  } else {
    if (th.textContent)  valuetext=th.textContent;
    else valuetext=th.innerText;    

    addthinlist(opto,valuetext, thid,false);
    
    th.setAttribute("selected","1");
    inversecolor(th);
  }

}

function unselecttree(tree) {  

  var as=tree.getElementsByTagName('a');

  for (var i=0;i<as.length;i++) {
    asel=as[i].getAttribute('selected');
    
    if (asel=='1') {
	inversecolor(as[i]);      
	as[i].setAttribute("selected","0");
      }
  }    
}
function hidethtree(idsel,idtree,oix) {
  var inpsel=document.getElementById(idsel); 
  var tree=document.getElementById(idtree); 
  var hasel=false;
  if (inpsel) {
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].selected) hasel=true;
    }
  }
  if (tree && (!hasel)) {
    tree.style.display='none';
  }
  if (oix) oix.disabled=true;

}
function addthinlist(sel,value,key,notselected) {

  if (isNetscape) pos=null;
  else pos=sel.options.length+1;
  if (! key) key=value;
  sel.add(new Option(value,key, false, true),pos);
  if (notselected) {
    sel.options[sel.options.length - 1].selected=false;
  }
}

function removethinlist(inpsel,key) {
  if (inpsel) {
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].value==key) inpsel.remove(k--);
    }    
  }
}
function filtertreesearch(event,filter,cible) {

  if (cible) {   
    var sels=cible.getElementsByTagName('select');
    var thid,multi,aid,famid;
    for (var i=0;i<sels.length;i++) {
      thid=sels[i].getAttribute('thesaurus');
      multi=sels[i].getAttribute('multiple');
      famid=sels[i].getAttribute('famid');
      aid=sels[i].getAttribute('aid');
    }
   
    cible.style.visibility='visible';
    cible.style.display='';
    clipboardWait(cible);

    setTimeout(function() {sendfiltertreesearch(event,cible,thid,filter,multi,famid,aid);},10); // force event loop to view waiting effects         
  }
}

function sendfiltertreesearch(event,cible,thid,filter,multi,famid,aid) {
  var corestandurl=window.location.pathname+'?sole=Y';

  if (cible) {          
    var url=corestandurl+'&app=THESAURUS&action=EDITTREESEARCH&thid='+thid+'&filter='+filter+'&famid='+famid+'&aid='+aid;
    enableSynchro();
    if (multi) url = url + '&multi=yes';
    var ret=requestUrlSend(cible,url); 
    disableSynchro();
    cible.style.visibility='visible';
    cible.style.display='';

    //    resizeme(event,cible.id);        
  }
}

function changeCharTHHide(attrid, inpsel) {
  var iinput=document.getElementById('ix_'+attrid);
  var filter=document.getElementById('ifilter_'+attrid);
  var needdisable=true;
  if (iinput) {
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].selected) needdisable=false;
    }
    if (needdisable) {
      iinput.value=iinput.getAttribute('uvalue');
      iinput.title=iinput.getAttribute('utitle');
      
      if (filter && filter.style.display=='none')  iinput.disabled=true;
    } else {
      iinput.value=iinput.getAttribute('svalue');
      iinput.title=iinput.getAttribute('stitle');
      iinput.disabled=false;
    }
		       
  }
}


function closeOrUnselectTH(aid) {
  

  hidethtree('thopt_'+aid,'ifilter_'+aid,this);
  clearDocIdInputs(aid,'thopt_'+aid,false);
  preselectMultiTree('thtree'+aid,aid);
  changeCharTHHide(aid,document.getElementById('thopt_'+aid));
}

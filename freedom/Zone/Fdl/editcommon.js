var isNetscape = navigator.appName=="Netscape";
// auxilarry window to select choice
var wichoose= false;

// current instance
var colorPick = new ColorPicker();
initDHTMLAPI();


document.isCancelled=false;
document.isSubmitted=false;

function scruteadocs() { 
  var newTa = document.getElementsByTagName('iframe');
  for (var i=0; i < newTa.length; i++){ 
    ofr=newTa[i];
    updateOfr(ofr,true);
  }
}

function updateIfr(ifr) { 
  var ofr=document.getElementById(ifr);
  if (ofr) updateOfr(ofr,false);
}

function updateOfr(ofr,onlyclose) {    
  if (ofr.id.substr(0,4)=="ifr_") {
	  
      iid=ofr.id.substr(4);
      viid=document.getElementById(iid);
      if (viid) {
	nurl=ofr.src;
	if ((viid.value==' ') || (viid.value=='')) {
	  nurl='about:blank';
	  ofr.style.display='none';
	} else {
	  if (!onlyclose)  nurl='[CORE_STANDURL]&app=FDL&action=IMPCARD&zone=FDL:VIEWTHUMBCARD:T&id='+viid.value;
	}

	if (ofr.src.substr(-10) != nurl.substr(-10) ) {
	  ofr.src=nurl;	      
	  if (nurl != 'about:blank') {
	    ofr.style.display='';  
	    sdisplay='none';
	  } else {
	    sdisplay='';
	  }
	  nnode=ofr.nextSibling;
	  while (nnode && (nnode.nodeType != 1)) nnode = nnode.nextSibling; //case TEXT node
	  nnode.style.display=sdisplay;
	      
	}
      }
    }
  
}

setInterval('scruteadocs()',1000);
// search the row number of an element present in array
function getRowNumber(el) {
  var tr=el;


  // find the row
  while ((tr != null) && (tr.tagName != 'TR')) {
    
    tr=tr.parentNode;
  }

  // up to table
  var nrow=0;
  tr=tr.previousSibling;
  while ((tr != null) && ((tr.nodeType != 1) || (tr.tagName == 'TR'))) {
    if (tr.nodeType == 1) nrow++;
    tr=tr.previousSibling;
  }
  return nrow;
}

var enuminprogress=false;
function sendEnumChoice(event,docid,  choiceButton ,attrid, sorm,options) {


  var inp  = choiceButton.previousSibling;
  var index='';
  var attrid;
 
  var domindex=''; // needed to set values in arrays
  // search the input button in previous element
 
  var inid;

  if (enuminprogress) return;
  enuminprogress=true;  
  //  inid= choiceButton.id.substr(3);
  inp=document.getElementById(attrid);


  if ((! inp)||(inp==null)) {
    alert('[TEXT:enumerate input not found]'+':'+attrid);
  }


  


  if (inp.name.substr(inp.name.length-2,2) == '[]') {
    // it is an attribute in array
    attrid=inp.name.substr(1,inp.name.length-3);
    index=getRowNumber(choiceButton);
    domindex = inp.id.substring(attrid.length);    
  } else {
    attrid=inp.name.substr(1,inp.name.length-1);;
  }

  if (! options) options='';

  f =inp.form;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=FDL&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&index='+index+'&domindex='+domindex+options;

  

  var xy= getAnchorWindowPosition(inp.id);

  if (isNaN(window.screenX)){
    xy.y+=15; // add supposed decoration height
    // add body left width for IE sometimes ...
    if (parent.ffolder)  xy.x += parent.ffolder.document.body.clientWidth;
    
  }


  wichoose = window.open('', 'wchoose', 'scrollbars=yes,resizable=yes,height=30,width=290,left='+xy.x+',top='+xy.y);
  wichoose.focus();

  wichoose.moveTo(xy.x, xy.y+10);
  f.target='wchoose';


  enableall();
  f.submit();
  restoreall();
  disableReadAttribute();
  // reset to initial action
  f.action=oldact;
  f.target=oldtar;

  enuminprogress=false;
}

function enableall() {

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) {	
           elements[i].oridisabled=elements[i].disabled;
           elements[i].disabled=false;
       }
  }
}
function restoreall() {

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) {	
           elements[i].disabled=elements[i].oridisabled;
       }
  }
}

// tranfert value from s to d
function transfertValue(s,d) {
  var sob=document.getElementById(s);
  var dob=document.getElementById(d);
  if (sob && dob) {
    dob.value=sob.value;
  }
}
function resizeInputFields() {


  var w, newsize;

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) { 
         if (elements[i].className == 'autoresize') {
	   w=getObjectWidth(elements[i].parentNode);
	   if (w > 45) {
	     newsize = (w - 45) / 9;
	     if (newsize < 10) newsize=10; //min size is 10 characters
	     if (elements[i].type == 'text')
	       elements[i].size=newsize;
	     if (elements[i].type == 'textarea')
	       elements[i].cols=newsize;
	   }
	 }
       }
  }
}

// close auxillary window if open
function closechoose() {

    if (wichoose) wichoose.close();
}

function canmodify() {
    var err='';
    var v;
    for (var i=0; i< attrNid.length; i++) {
      e=document.getElementById(attrNid[i]);
      if (!e) e=document.getElementById('_'+attrNid[i]);
	if (e) {
	  v=getIValue(e);
	  if (v === false) {
	    ta = document.getElementsByName('_'+attrNid[i]+'[]');
	    if (ta.length == 0)	err += ' - '+attrNtitle[i]+'\n';
	    for (var j=0; j< ta.length; j++) {
	      v=getIValue(ta[j]);
	      if ((v === '')||(v === ' ')) err +=  ' - '+attrNtitle[i]+'/'+(j+1)+'\n';
	    }
	  } else {
	    if ((v === '')||(v === ' ')) err +=  ' - '+attrNtitle[i]+'\n';
	  }
        } else {
	  // search in multiple values
	  v=getInputValues(attrNid[i]);
	  
	  if ((v!==false) && ((v === '')||(v === ' '))) err +=  ' - '+attrNtitle[i]+'\n';
	}
    }
    if (err != '') {
	    alert('[TEXT:these needed attributes are empty]\n'+err);
	    return false;
    }
    return true;
}

// to define which attributes must be disabled
var tain= new Array();
var taout= new Array();
[BLOCK RATTR]
tain[[jska]]=[jstain];
taout[[jska]]=[jstaout];
[ENDBLOCK RATTR]

function getInputValue(id,index) {
  if (!index) index=0;
  if (document.getElementById(id)) {
    return document.getElementById(id).value;
  } else {
    
    if (isNetscape) le = document.getElementsByName('_'+id+'[]');
    else le = getInputsByName('_'+id);
    if ((le.length - 1) >= index) {
      return le[index].value;
    }    
  }
  return '';
}

// return values for input multiples 
function getInputValues(n) {
 var v='';
 var ta;
 if (isNetscape) ta=  document.getElementsByName('_'+n+'[]');
 else  ta = getInputsByName('_'+n);
 if (ta.length==0) ta = document.getElementsByName('_'+n);
 if (ta.length==0) return false;
  for (var j=0; j< ta.length; j++) {
    switch (ta[j].type) {
    case 'radio':
      if (ta[j].checked) v=ta[j].value;
      break;
    case 'checkbox':
      if (ta[j].checked) v=ta[j].value;
      break;
    }

  }
  return v;
}
function getInputLocked() {
  var tlock=new Array();
  if (tain) {
  for (var c=0; c< tain.length; c++) {
    ndis = true;
    for (var i=0; i< tain[c].length; i++) {
      vin = getInputValue(tain[c][i]);
      if ((vin == '') || (vin == ' ')) ndis = false;
    }
    if (ndis) {
      // the attribute can lock others

      tlock=tlock.concat(taout[c]);
    }
  }
  }
 
  return (tlock);
}

function getInputsByName(n) {
  var ti= document.getElementsByTagName("input");    
  var t = new Array();
  var ni;
  var pos;
	
  for (var i=0; i< ti.length; i++) { 
    pos=ti[i].name.indexOf('[');
    if (pos==-1) ni=ti[i].name;
    else ni=ti[i].name.substr(0,pos);
    if ((ni == n) && (ti[i].name.substr(ti[i].name.length-4,4) != '[-1]')) {	
     
      t.push(ti[i]);
    }
  }

  if (t.length == 0) { 
    // try with select
    ti= document.getElementsByTagName("select");
    
    for (var i=0; i< ti.length; i++) {       
      pos=ti[i].name.indexOf('[');
      if (pos==-1) ni=ti[i].name;
      else ni=ti[i].name.substr(0,pos);
      if ((ni == n) && (ti[i].name.substr(ti[i].name.length-4,4) != '[-1]')) {		
	t.push(ti[i]);
      }
    }      
  }  

  if (t.length == 0) { 
    // try with select
    ti= document.getElementsByTagName("textarea");
    
    for (var i=0; i< ti.length; i++) {       
      pos=ti[i].name.indexOf('[');
      if (pos==-1) ni=ti[i].name;
      else ni=ti[i].name.substr(0,pos);
      if ((ni == n) && (ti[i].name.substr(ti[i].name.length-4,4) != '[-1]')) {		
	t.push(ti[i]);
      }
    }      
  }
  
  return t;
}

function getIValue(i) {
  if (i) {
    if (i.tagName == "TEXTAREA") return i.value;
    if (i.tagName == "INPUT") {
      if ((i.type=='radio')||(i.type=='checkbox')) return i.checked;      
      return i.value;
    }
    if (i.tagName == "SELECT") {
      if (i.selectedIndex >= 0)   return i.options[i.selectedIndex].value;
      else return '';
    } 
  }
  return false;
}
function setIValue(i,v) {
  if (i) {
   
    if (i.tagName == "INPUT") {
      if ((i.type=='radio')||(i.type=='checkbox')) {
	i.checked=v;
	if (v && (i.type=='radio')) changeCheckClasses(i,false);
      }
      else i.value=v;
    }
    else if (i.tagName == "TEXTAREA")  i.value=v;
    else  if (i.tagName == "SELECT") {
      for (var k=0;k<i.options.length;k++) {
	if (i.options[k].value == v) i.selectedIndex=k;
      }
    }
  }
}

function isInputLocked(id) {
  var tlock=new Array();
  for (var c=0; c< tain.length; c++) {
    ndis = true;
    for (var i=0; i< tain[c].length; i++) {
      vin = getInputValue(tain[c][i]);
      if ((vin == '') || (vin == ' ')) ndis = false;
    }
    if (ndis) {
      // the attribute can lock others
      
      for (var i=0; i< taout[c].length; i++) {
	//	alert(tain[c][i] + '/' + id);
	if (taout[c][i] == id) return true;
      }
    }
  }
  return false;;
}


function disableReadAttribute() {
    
  var ndis = true;
  var i;
  var vin;
  var lin;
  var inx,inc; // input button
  if (tain) {
  for (var c=0; c< tain.length; c++) {
    ndis = true;
    for (var i=0; i< tain[c].length; i++) {
      vin = getInputValue(tain[c][i]);

      if ((vin == '') || (vin == ' ')) ndis = false;
      
    }
    for (var i=0; i< taout[c].length; i++) {
      if (document.getElementById(taout[c][i])) {
	if (document.getElementById(taout[c][i]).type != 'hidden') {
	  document.getElementById(taout[c][i]).disabled=ndis;
	  inc=document.getElementById('ic_'+taout[c][i]);
	  inx=document.getElementById('ix_'+taout[c][i]);
	  if (inc) inc.disabled=ndis;
	 
	  if (ndis) {
	    // document.getElementById(taout[c][i]).style.backgroundColor='[CORE_BGCOLORALTERN]';
	    //if (inc)  inc.style.backgroundColor='[CORE_BGCOLORALTERN]';	      	    
	  } else {
	    
	    if (inc) inc.style.backgroundColor='';
	    //if (document.getElementById(taout[c][i]).style.backgroundColor == '[CORE_BGCOLORALTERN]')
	    document.getElementById(taout[c][i]).style.backgroundColor == '';
	  }
	}
      } else {
	// search in arrays
	if (isNetscape) lin = document.getElementsByName('_'+taout[c][i]+'[]');
	else lin = getInputsByName('_'+taout[c][i]);
	//	alert(taout[c][i]+'/'+lin.length);
	//	alert(document.getElementsByTagName('input').length);
	//alert(getInputsByName('_'+taout[c][i]).length);
	for (var j=0; j< lin.length; j++) {
	  ndis=true;
	  for (var k=0; k< tain[c].length; k++) {
	    vin = getInputValue(tain[c][k],j);
	    if ((vin == '') || (vin == ' ')) ndis = false;
	    
	  }
	  //	  alert(tain[c].toString()+'['+j+']'+ndis);
	  if (lin[j].type != 'hidden') {
	    lin[j].disabled=ndis;
	    //lin[j].style.backgroundColor=(ndis)?'[CORE_BGCOLORALTERN]':'';		
	  }
		
	}
      }
      }
    }
  }
 
}

function editOnLoad() {
    resizeInputFields();
    disableReadAttribute();
}


function clearInputs(tinput, idx,attrid) {
  var iinput;
  var err='';
 
  for (var i=0; i< tinput.length; i++) {
    iinput=tinput[i]+idx;
   
    if (document.getElementById(iinput)) {
      if (! isInputLocked(iinput)) {	
	document.getElementById(iinput).value=' ';
	//	document.getElementById(iinput).style.backgroundColor='[CORE_BGCOLORHIGH]';
	
      } else {
	err = err + "\n" + iinput;
      }
    } else {
      if (! document.getElementById(iinput+'0'))   alert('[TEXT:Attribute not found]'+' : '+iinput);
    }
  }
  disableReadAttribute();

  if (err != '')  alert('[TEXT:NOT Clear]'+err);
  if (attrid && document.getElementById(attrid)) document.getElementById(attrid).focus();

}


function unselectInput(id) {
  var sel=document.getElementById(id);
  if (sel) {
    for (var i=0; i< sel.options.length; i++) {
      sel.options[i].selected=false;
    }
  }
  sel.options[sel.options.length-1].selected=true;
}
function autoUnlock(docid) {
  var r;
  var corestandurl=window.location.pathname+'?sole=Y';
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    r = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version     
    r = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (r) {     
    r.open("GET", corestandurl+'&app=FDL&action=UNLOCKFILE&auto=Y&autoclose=Y&id='+docid,false);
    //      req.setRequestHeader("Content-length", "1");     
    r.send('');
    if(r.status == 200) { 
      if (r.responseXML) {
	var xmlres=r.responseXML;
	var elts = xmlres.getElementsByTagName("status");
	if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var delay=elt.getAttribute("delay");
	  var w=elt.getAttribute("warning");
	  
	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+req.responseText);
	    return false;
	  }	
	  return true;
	}
      }
      else {
	alert('no xml\n'+r.responseText);
      } 
    }    
  }  	
  return false;  
}
function submitEdit(event) {
  var fedit= document.getElementById('fedit');
     var r=true;
   if (fedit) {
     var fedit= document.getElementById('fedit');
     if (fedit.onsubmit) r=fedit.onsubmit();
     if (r) fedit.submit();
   }
   return r;
}

function pleaseSave(event) {
  if (document.isChanged && (! document.isSubmitted) && (! document.isCancelled)) {
    if (confirm('[TEXT:Save changes ?]')) {
      var bsubmit= document.getElementById('iSubmit');

      var can=canmodify();//bsubmit.onclick.apply(null,[event]);

      if (can) {
	var fedit= document.getElementById('fedit');
	if (fedit.onsubmit) fedit.onsubmit();
	fedit.submit();
      
      } else {
	alert('[TEXT:Data cannot be saved]');
	return false;
      }
    }
  }
  
  return true;
  
}

var OattrNid=null; //original attrNid
var OattrNtitle=null; //original attrNtitle
var askState=null; // memo displayed state

function askForTransition(event) {
  var th=document.getElementById('seltrans');
  var state=getIValue(th);
  
  var wf=document.getElementById('hwfask');
  var nf=document.getElementById('wfask');
  var nfd=document.getElementById('dfask');
  var i;
  var ask=new Array();
  var tnf=new Array();
  var k=-1; // index for searches
  var xy;
  var nx;
  var h=0;
  
  if (askState == state) return;
  askState=state;
  if (OattrNid == null) {
    OattrNid=new Array();
    OattrNtitle=new Array();
    for (i=0;i<attrNid.length;i++) OattrNid.push(attrNid[i]);// memo  original
    for (i=0;i<attrNtitle.length;i++) OattrNtitle.push(attrNtitle[i]);
  }
  
    
  attrNid=new Array();
  attrNtitle=new Array();  
  for (i=0;i<OattrNid.length;i++) attrNid.push(OattrNid[i]);// restore original
  for (i=0;i<OattrNtitle.length;i++) attrNtitle.push(OattrNtitle[i]);
  

  if (askState) {
    // display or not comment area
    if (state != '-') {document.getElementById('comment').style.visibility='';} else document.getElementById('comment').style.visibility='hidden';

    // move button nodes
    for (i=0;i<nf.childNodes.length;i++) {
      if (nf.childNodes[i].id && (nf.childNodes[i].id.substring(0,3)=="TWF")) tnf.push(nf.childNodes[i]);
    }
    for (i=0;i<tnf.length;i++) {
      wf.appendChild(tnf[i]);
    }
    for (i=0;i<states.length;i++) {
      if (states[i]==askState) {
	ask=askes[i];
      }
    }

    for (i=0;i<ask.length;i++) {
      twf=document.getElementById('TWF'+ask[i]);
      nf.appendChild(twf);
      k=array_search(twf.id.substr(3),WattrNid);
      if (k >= 0) {
	attrNid.push(WattrNid[k]);
	attrNtitle.push(WattrNtitle[k]);
      }
    }
    if (ask.length > 0) {
      // search table
      ftable=th.parentNode;
      while (ftable && ((ftable.tagName!='TABLE')&&(ftable.tagName!='TBODY'))) ftable=ftable.parentNode;
      if (ftable) {
	yfoot=AnchorPosition_getPageOffsetTop(ftable);
      } else {
	yfoot=50;
      }
      GetXY(event);
      
      nfd.style.display='none';	
      nfd.style.display='';	// to refresh div
      	
      nfd.style.top='160px';
      //nf.style.top='300px';
      w=getObjectWidth(nfd);
      nx=Xpos-w+40;
      if (nx < 0) nx=0;
      nfd.style.left=nx+'px';
      if (yfoot < 100) {
	if (ftable) h=getObjectHeight(ftable);
	nfd.style.top=(yfoot+10+h)+'px';
      } else {
	
	//	alert(xy.y+'/'+h+'/'+(xy.y-h));
	hnf=getObjectHeight(nfd);
	nfd.style.top=(yfoot-hnf)+'px';

      }
      if (isNetscape) { // more beautifull
	  nfd.style.position='fixed';//h-=document.body.scrollTop; // fixed position
	  nfd.style.MozOpacity=0.02;
	  moz_unfade(nfd.id);
	}
      nfd.style.display='none';	
      nfd.style.display='';	// to refresh div
    } else nfd.style.display='none';
  }

}
// change for time attribute
function chtime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);
  var shh,smm,ihh,imm;
  if (t && hh && mm) {
    ihh=parseInt(hh.value * 1)%24;
    if (isNaN(ihh)) ihh=0;
    if (ihh < 10) shh='0'+ihh.toString();
    else shh=ihh.toString();
    hh.value=shh;

    imm=parseInt(mm.value * 1)%60;
    if (isNaN(imm)) imm=0;
    if (imm < 10) smm='0'+imm.toString();
    else smm=imm.toString();
    mm.value=smm;

    t.value=shh+':'+smm;
  }
}

// change for time attribute
function clearTime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);

  if (t && hh && mm) {
   
    hh.value='';hh.style.backgroundColor='[CORE_BGCOLORHIGH]';
    mm.value='';mm.style.backgroundColor='[CORE_BGCOLORHIGH]';

    t.value=' ';
  }
}

function checkinput(cid,check,iin) {    
  var i=document.getElementById(cid);
  if (i) {
    if (!i.disabled) {
      if (check) i.checked=check;
      else i.checked=(!i.checked);
      changeCheckClasses(i,iin);
    }
  }
}
// change style classes for check input
function changeCheckClasses(th,iin) {
  if (th && th.name) {
    var icheck=document.getElementsByName(th.name);
    if (icheck.length==0) {
      // other method for IE
      icheck=new Array();
      var ti=th.parentNode.parentNode.parentNode.getElementsByTagName('input');     
      for (var i=0;i<ti.length;i++) {
 	if (ti[i].name && (ti[i].name == th.name)) {
	  icheck.push(ti[i]);
 	}
      }
    }

    if (icheck.length==0) return;
    var  needuncheck=false;
    for (var i=0;i<icheck.length;i++) {
      if (icheck[i].checked) icheck[i].parentNode.parentNode.className='checked';
      else icheck[i].parentNode.parentNode.className='';
    }
    //alert(icheck[0].type);
   
    for (var i=0;i<icheck.length-1;i++) {
      if (icheck[i].checked) needuncheck=true;
    }
    icheck[icheck.length-1].checked=(!needuncheck);
    if (iin) {      
      for (var i=0;i<icheck.length;i++) {
	if (icheck[i].checked) {
	  var oi=document.getElementById(iin);
	  oi.value=icheck[i].value;
	}
      }
    }
  }
}

// change style classes for check bool input
function changeCheckBoolClasses(th,name) {
  if (th) {
    var icheck=new Array(2);
    var i=0;
    var p=th.previousSibling;
    while (p && (i<2)) {
      if (p.name == name) {	
	icheck[i]=p;
	i++;
      }
      p=p.previousSibling;
    }
    if (i==2) {
      icheck[1].checked=(!th.checked);
      icheck[0].checked=th.checked;
    } else {
      alert('[TEXT:changeCheckBoolClasses Error]');
    }

  }
}

// change checkbox value for boolean style
function changeCheckBoxCheck(oboolid,idx,th) {
  obool=document.getElementById(oboolid);
  obool.checked=(idx!='0');
  var i=0;
  var p=obool.previousSibling;
  while (p && (i<2)) {
    if (p.name == th.name) {	
      if (p.id != th.id) p.checked=false;
      i++;
    }
    p=p.previousSibling;
  }

}

function addinlist(sel,value) {

  if (isNetscape) pos=null;
  else pos=sel.options.length+1;
  sel.add(new Option(value, value, false, true),pos);
}

function  nodereplacestr(n,s1,s2) {
  
  var kids=n.childNodes;
  var ka;
  var avalue;
  var regs1;
  var rs1;
  var tmp;
  var attnames = new Array('onclick','href','onmousedown','onmouseover','id','name','onchange','oncontextmenu');
  // for regexp
  rs1 = s1.replace('[','\\[');
  rs1 = rs1.replace(']','\\]');
  regs1 = new RegExp(rs1,'g');
  
  for (var i=0; i< kids.length; i++) {     
    if (kids[i].nodeType==3) { 
      // Node.TEXT_NODE
      
	if (kids[i].data.search(rs1) != -1) {
	  tmp=kids[i].data; // need to copy to avoid recursive replace
	  
	  kids[i].data = tmp.replace(s1,s2);
	}
    } else if (kids[i].nodeType==1) { 
      // Node.ELEMENT_NODE
	
	// replace  attributes defined in attnames array
	  for (iatt in attnames) {
	    
	    attr = kids[i].getAttributeNode(attnames[iatt]);
	    if ((attr != null) && (attr.value != null) && (attr.value != 'null'))  {
	      
	      
	      if (attr.value.search(rs1) != -1) {				
		avalue=attr.value.replace(regs1,s2);

		if (isNetscape) attr.value=avalue;
		else if ((attr.name == 'onclick') || (attr.name == 'onmousedown') || (attr.name == 'onmouseover')) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
		else attr.value=avalue;
	      }
	    }
	  }
      nodereplacestr(kids[i],s1,s2);
    } 
  }
}



//-------------------------------------------------------------
// select tr (row table) 
var seltr=false; 

function addtr(trid, tbodyid) {
  
  var ntr;
  with (document.getElementById(trid)) {
    // need to change display before because IE doesn't want after clonage
    style.display='';

    ntr = cloneNode(true);
    style.display='none';
    if (isNetscape) {
      // bug :: Mozilla don't clone textarea values
      var newTa = ntr.getElementsByTagName('textarea');
      for (var i=0; i < newTa.length; i++){ 
	
	newTa[i].setAttribute('value',getElementsByTagName('textarea')[i].value);
	// -- this next line is for N7 + Mozilla
	newTa[i].defaultValue = getElementsByTagName('textarea')[i].value;
      }
    }
  }
  
  ntr.id = '';
  ntable = document.getElementById(tbodyid);
  ntable.appendChild(ntr);

  nodereplacestr(ntr,'-1]',']'); // replace name [-1] by []
  nodereplacestr(ntr,'-1',ntable.childNodes.length);
  resizeInputFields(); // need to revaluate input width
 
  if (seltr && (seltr.parentNode == ntr.parentNode))  {
    seltr.parentNode.insertBefore(ntr,seltr);
    resetTrInputs(ntr);
  } else {
    var ltr = ntable.getElementsByTagName('tr');
    var ltrfil=new Array();
    for (var i=0;i<ltr.length ;i++) {
      if ((ltr[i].parentNode.id == tbodyid) || (ltr[i].parentNode.parentNode.id == tbodyid)) ltrfil.push(ltr[i]);
    }
    if (ltrfil.length > 1) ltrfil[ltrfil.length-2].parentNode.insertBefore(ntr,ltrfil[ltrfil.length-2]);
  }
  return ntr;
  
}

// use to delete an article
function deltr(tr) {


  tr.parentNode.removeChild(tr);

  return;
  
}

function resetInputsByName(name) {
  if (! isNetscape) return;
  var la=document.getElementsByName(name);
  if (la) {
    for (var i=0; i< la.length; i++) { 
	    la[i].parentNode.insertBefore(la[i],la[i].nextSibling);      
	  }
  }
}

function resetTrInputs(tr) {
  if (! isNetscape) return;
  var tin = tr.getElementsByTagName('input');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }

  // add select input also
  tin = tr.getElementsByTagName('select');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }
  // add select input also
  tin = tr.getElementsByTagName('textarea');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }

}
// up tr order 
function uptr(trnode) {

  var pnode = trnode.previousSibling;
  var textnode=false;

  while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??

  if (pnode)  {
    trnode.parentNode.insertBefore(trnode,pnode);
    
  }  else {
    trnode.parentNode.appendChild(trnode); // latest (cyclic)
  }
  resetTrInputs(trnode);
  return;  
}

// down tr order 
function downtr(trnode) {

  var nnode = trnode.nextSibling;

  while (nnode && (nnode.nodeType != 1)) nnode = nnode.nextSibling; // case TEXT attribute in mozilla between TR ??

  if (nnode ) {
      nnnode = nnode.nextSibling; 
      while (nnnode && (nnnode.nodeType != 1)) nnnode = nnnode.nextSibling; // case TEXT attribute in mozilla between TR ??

      if (nnnode) 
         trnode.parentNode.insertBefore(trnode,nnnode);
      else 
         trnode.parentNode.appendChild(trnode); // latest
  } else {
      trnode.parentNode.insertBefore(trnode,trnode.parentNode.firstChild); // latest (cyclic)
  }


  resetTrInputs(trnode);
  return;  
}

// use to delete an article
var specDeltr=false;
function delseltr() {
  if (seltr) {
    seltr.parentNode.removeChild(seltr);  
    if (specDeltr) {
      eval(specDeltr);
      try {
      }
      catch(exception) {
	alert(exception);
      }
    }
  }
  unseltr();
  return;
  
}
var specDuptr=false;
function duptr() {
  var dsel;
  var tbodysel;
  var i;
  if (seltr) {
    tbodysel=seltr.parentNode;
    tbodyselid=tbodysel.id;
    tnewid='tnew'+tbodyselid.substr(5);
    if (document.getElementById(tnewid)) {
      ntr=addtr(tnewid,tbodyselid);
      afterCloneBug(seltr,ntr);
    
    } else {
      // direct clone tr
      csel=seltr.cloneNode(true);
      csel.style.backgroundColor='';
      seltr.parentNode.insertBefore(csel,seltr);
      visibilityinsert('trash','hidden');
      // after clone (correct bug)
      afterCloneBug(seltr,csel);
    }
    disableReadAttribute();
    if (specDuptr) {
      eval(specDuptr);
      try {
      }
      catch(exception) {
	alert(exception);
      }
    }
  }  
}

function afterCloneBug(o1,o2) {
  var ti1,ti2,t;
  var itag=new Array('input','textarea','select');

  for (t in itag) {
    ti1= o1.getElementsByTagName(itag[t]);
    ti2= o2.getElementsByTagName(itag[t]);
    for ( i=0; i< ti1.length; i++) {
      setIValue(ti2[i],getIValue(ti1[i]));
    }
  }
}

// change input (id) value (v) in node n
function chgInputValue(nid,id,v) {
  
  var itag=new Array('input','textarea','select');
  var ti,t;
  var n=document.getElementById(nid);

  if (n) {
    for (t in itag) {
      ti=n.getElementsByTagName(itag[t]);
      for (var i=0; i< ti.length; i++) {
	pos=ti[i].name.indexOf('[');
	if (pos==-1) ni=ti[i].name;
	else ni=ti[i].name.substr(0,pos);
	if (ni==id) {
	  setIValue(ti[i],v);
	}
      }
    
    }
  }
  
  
}
function visibilityinsert(n,d) {
  var ti = document.getElementsByName(n);
  for (var i=0; i< ti.length; i++) { 
    ti[i].style.visibility=d;
  }
}

function selecttr(o,tr) {

  visibilityinsert('trash','hidden');
  visibilityinsert('unselect','hidden');
  var ti = tr.parentNode.getElementsByTagName('img');
  for (var i=0; i< ti.length; i++) { 
    if (ti[i].name=='unselect') ti[i].style.visibility='visible';
  }
  var ti = tr.parentNode.getElementsByTagName('textarea');
  for (var i=0; i< ti.length; i++) { 
    ti[i].rows=1;
    if (ti[i].id && document.getElementById('exp'+ti[i].id)) document.getElementById('exp'+ti[i].id).style.display='none';
  }
  if (seltr) {
    seltr.className='';
    
  }   
  o=o.previousSibling;
  while (o && (o.nodeType != 1)) o = o.previousSibling; // case TEXT attribute in mozilla between TR 
  if (!o) alert('[TEXT:no trash image]');
  else o.style.visibility='visible';

  seltr=tr;

  seltr.className='selecta';


  return;  
}

//unselect selected
function unseltr() {

  if (seltr) {
    seltr.className='';
    
    visibilityinsert('insertup','hidden');
  }
  visibilityinsert('trash','hidden');
  visibilityinsert('unselect','hidden');
  seltr=false;

  return;  
}
var specMovetr=null;
function movetr(tr) {

  var trnode= seltr;
  var pnode = tr;
  if (seltr) {  

    while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??
    if (pnode)  {
      trnode.parentNode.insertBefore(trnode,pnode);
    
    }  else {
      //trnode.parentNode.appendChild(trnode); // latest (cyclic)
    }
    
    resetTrInputs(trnode);
    if (specMovetr) {
      eval(specMovetr);
      try {
      }
      catch(exception) {
	alert(exception);
      }
    }
  }
  return;  
}




//-----------------------------------------
function submitinputs(faction, itarget) {
  var fedit = document.fedit;
  //  resetInputs();
  
  with (document.modifydoc) {
    var editAction=action;
    var editTarget=target;
    wf=subwindow(30,390,itarget,'about:blank');
    enableall();  
    target=itarget;
    action=faction;
    submit();
    restoreall();
    target=editTarget;
    action=editAction;
        
    return wf;
  }
}


function vconstraint(cButton,famid,attrid) {
  var inp  = cButton.previousSibling;
  var index='';

  var domindex=''; // needed to set values in arrays
  // search the input button in previous element

  var inid;
  var wf;

  
  inid= cButton.id.substr(3);
  inp=document.getElementById(inid);

  if ((! inp)||(inp==null)) {
    alert('[TEXT:vconstraint input not found id=]'+inid);
  }

  

  if (inp.name.substr(inp.name.length-2,2) == '[]') {
    // it is an attribute in array
    index=getRowNumber(cButton);
    domindex = inp.id.substring(attrid.length);    
  }

  
  wf=submitinputs('[CORE_STANDURL]&app=FDL&action=VCONSTRAINT&famid='+famid+'&attrid='+attrid+'&index='+index+'&domindex='+domindex,'wchoose');

  var xy= getAnchorWindowPosition(inp.id);

  if (isNaN(window.screenX)){
    xy.y+=15; // add supposed decoration height
    // add body left width for IE sometimes ...
    if (parent.ffolder)  xy.x += parent.ffolder.document.body.clientWidth;
    
  }
  wf.moveTo(xy.x, xy.y+10);
}

function viewoption(aid,index,fid,said) {
  nfid=document.getElementById(fid);
  naid=document.getElementById(aid+index);
  nval=document.getElementById(said);
  pdivopt=document.getElementById('pdiv_'+said);

  if (nfid && naid) {
    docid=naid.value;
    if (parseInt(docid) > 0) {
      val=escape(nval.value);
      nfid.src='[CORE_STANDURL]&app=FDL&action=EDITOPTION&id='+docid+'&aid='+said+'&opt='+val;
      nfid.style.display='';
      pdivopt.style.display='none';
    } else {
      alert('[TEXT:Choose document before set options]');
    }
  }
}
function canceloption(said) {
  nfid=self.parent.document.getElementById('if_'+said);
  pdivopt=self.parent.document.getElementById('pdiv_'+said);

  if (nfid && pdivopt) {
      pdivopt.style.display='';
      nfid.style.display='none';
      nfid.src='about:blank';    
  }
}


// to adjust height of body in edit card in fixed positionning
function fixedPosition() {
  var fspan=document.getElementById('fixspanhead');
  var ftable=document.getElementById('fixtablehead');
  var xy;
  var h;


  if ((document.body.scrollHeight) <= document.body.clientHeight) {    
    if (fspan && ftable) {
      ftable.style.position='static';
      fspan.style.display='none';
    }
    fspan=document.getElementById('fixspanfoot');
    ftable=document.getElementById('fixtablefoot');
    if (fspan && ftable) {
      ftable.style.position='static';
      fspan.style.display='none';
    }
  } else {;     
    if (fspan && ftable) {
      xy=getAnchorPosition(ftable.id);
      h=parseInt(getObjectHeight(ftable))-xy.y;
      if (h>0) {
	fspan.style.height=getObjectHeight(ftable);
	fspan.style.top=xy.y;
      }
    }
    fspan=document.getElementById('fixspanfoot');
    ftable=document.getElementById('fixtablefoot');

    if (fspan && ftable) {
      fspan.style.height=parseInt(getObjectHeight(ftable))+'px';;
    
    }
  }
}

function focusFirst() {
  
  var fedit= document.getElementById('fedit');
  if (fedit) {
    for (var i=0;i<fedit.elements.length;i++) {
      
      switch (fedit.elements[i].type) {
      case 'text':
      case 'select-one':
      case 'select-multiple':
      case 'textarea':
      case 'FIELDSET':
	if (! fedit.elements[i].disabled) {
	  fedit.elements[i].focus();
	  return;
	}
	break;
      case 'hidden':
      case 'file':
      case 'button':
      case 'submit':
      case 'radio':
      case 'undefined':
      case '':
	break;
      default:		  
	;
      }
    }
  }
}
if (isNetscape) addEvent(window,"load",fixedPosition);

// move inputs buttons from node to node
function mvbuttons(idnode1, idnode2) {
  var node1=document.getElementById(idnode1);
  var node2=document.getElementById(idnode2);
  var ti;
  var fc;
  if (node1 && node2) {
     ti= node1.getElementsByTagName("input");  
     fc=node2.firstChild;
     while (ti.length>0) {
       node2.insertBefore(ti[0],fc);
     }
  }

  
}


function mvbuttonsState() {
  var isub=document.getElementById('iSubmit');
  if (isub) isub.style.display='none';

  mvbuttons('editstatebutton','editbutton');
  
}

function preview(faction,ntarget) {
  var fedit = document.fedit;
  //resetInputs();
  
  with (document.modifydoc) {
    var editAction=action;
    var editTarget=target;
    if (! ntarget) ntarget='preview';
    wf=subwindowm(300,600,ntarget,'about:blank');
    enableall();  
    var na=document.getElementById('newart');
    if (na) {
      disabledInput(na,true);        
      var nt=document.getElementById('newtxt');
      disabledInput(nt,true);
    }
    target=ntarget;
    action=faction;

    submittextarea();
    submit();
    target=editTarget;
    action=editAction;
    restoreall()
    
    if (na) {
      disabledInput(na,false);            
      disabledInput(nt,false);
    }    
  }
}

function quicksave() {
  if (canmodify()) {
    with (document.modifydoc) {    
      var editTarget=target;
      if (isNaN(id.value) ) {
	alert('[TEXT:quick save not possible]');	
      } else {
	enableall();  
	var na=document.getElementById('newart');
	if (na) {
	  disabledInput(na,true);        
	  var nt=document.getElementById('newtxt');
	  disabledInput(nt,true);
	}
	target='fhsave';
	document.modifydoc.noredirect.value=1;
	// for htmlarea
	submittextarea();	
    

	submit();
	document.modifydoc.noredirect.value=0;
	document.isChanged=false;
	target=editTarget;
	restoreall()
    
	  if (na) {
	    disabledInput(na,false);            
	    disabledInput(nt,false);
	  }    
	viewwait(true);    
	return true;
      }
    }
  }
  return false;
}

function submittextarea() {
  // for htmlarea
  return;
  for (var i=0;i< editors.length; i++) {
    editors[i]._formSubmit();
  }
}
function viewquick(event,view) {
  if (! event) event=window.event;
  if (document.modifydoc.id.value > 0) {
    var ctrlKey = event.ctrlKey;
  
    if (view && ctrlKey) {
      document.getElementById('iQuicksave').style.display='';
      document.getElementById('iSubmit').style.display='none';
    }
    if (!view) {
      document.getElementById('iQuicksave').style.display='none';
      document.getElementById('iSubmit').style.display='';
    }
  }
}

addEvent(document,"keypress",trackKeysStop); // only stop propagation
addEvent(document,"keydown",trackKeys);
//addEvent(document,"keypress",trackKeys);

// ~~~~~~~~~~~~~~~~~ for ARRAY inputs ~~~~~~~~~~~~~
function trackKeysStop(event) {
  return(trackKeys(event,true));
}
function trackKeys(event,onlystop)
{
  var intKeyCode;
  var stop=false;
  var tm;
  if (isNetscape) {
    intKeyCode = event.which;
    if (!intKeyCode) intKeyCode= event.keyCode;
    altKey = event.altKey
    ctrlKey = event.ctrlKey
   }  else {
    intKeyCode = window.event.keyCode;
    altKey = window.event.altKey;
    ctrlKey = window.event.ctrlKey
   }
  window.status=intKeyCode + ':'+altKey+ ':'+ctrlKey;
  if (!onlystop) {
    if (((intKeyCode == 83)||(intKeyCode == 22)) && (altKey || ctrlKey)) {
      // Ctrl-S
      quicksave(); 
      stop=true;
    }
  }
  if ((!onlystop) && seltr ) {
    if (((intKeyCode == 86)||(intKeyCode == 22)) && (altKey || ctrlKey)) {
      // Ctrl-V
      duptr();
      stop=true;
    }
    
    if (((intKeyCode == 68)||(intKeyCode == 100)) && (altKey || ctrlKey)) {
      // Ctrl-D
       delseltr();
      stop=true;
    }
    if ( (intKeyCode == 38) && (altKey || ctrlKey)) {
      // Ctrl-Up
      tm=seltr.previousSibling; 
      while (tm && (tm.nodeType != 1)) tm = tm.previousSibling;
      if (tm) movetr(tm);
      stop=true;
    }
    if ((intKeyCode == 40) && (altKey || ctrlKey)) {
      // Ctrl-Down
      tm=seltr.nextSibling;
      while (tm && (tm.nodeType != 1)) tm = tm.nextSibling;
      tm=tm.nextSibling;
      while (tm && (tm.nodeType != 1)) tm = tm.nextSibling;
      if (tm) movetr(tm);
      stop=true;
    }
  }
  if (onlystop ) {
    if (altKey || ctrlKey) {
      if ((seltr && ((intKeyCode == 100) || 
		     (intKeyCode == 118))) ||
	  (intKeyCode == 115)) {
	stop=true;
      }
    }
  }



  if ( stop) {
    stopPropagation(event);
    return false;
  } 
    
  return true;
}
var dro=null; // clone use to move
var idro=null; // real tr to move
var hidro=null; // height of idro
var ytr=0;
var draggo=false;


function adraggo(event) {
  if (dro) {
    if (idro) {
      idro.style.visibility='hidden'; 
      var ti=dro.getElementsByTagName('input');    
      for (var i=0;i<ti.length;i++) { // to avoid conflict with others inputs
	ti[i].id='';
	ti[i].name='';
	ti[i].disabled=true;
      }
      
      idro.parentNode.appendChild(dro); 
      visibilityinsert('trash','hidden');
    }
    //    dragtr(event); 
    draggo=true;
  }
}

function increaselongtext(oid) {
  var o=document.getElementById(oid);
  var ip=document.getElementById('exp'+oid);

  if (o) {
    if ((o.scrollHeight-3) > o.clientHeight) {
      o.rows=9;
      if (ip) ip.style.display='';
    }
    
  }
}
function adrag(event,o) {
  sdrag(event); // in case of already in drag
  GetXY(event);
  dro=o.parentNode.parentNode.cloneNode(true);
  dro.style.position='absolute';
  dro.className='move';
  dro.style.width=getObjectWidth(o.parentNode.parentNode);
  idro=o.parentNode.parentNode;
  hidro=getObjectHeight(idro);
  dro.style.top=Ypos-Math.round(hidro/2);
  ytr=Ypos;  
  addEvent(document,"mousemove",dragtr); 
  stopPropagation(event);

  setTimeout('adraggo()',300); 
  //adraggo(event);
}
function sdrag(event) {
  var dytr; //delta
  if (dro && draggo) {
    if (dro.parentNode) dro.parentNode.removeChild(dro);
    GetXY(event); 
    dytr=Ypos-ytr;
    if (dytr > 0) dytr=dytr-(hidro/2);
    dtr=Math.round(dytr/hidro);
    //alert(hidro+'/'+dytr+'/'+dytr/hidro+'/'+dtr);
    
    trmo=idro;
    if (dtr > 0) {
      while (trmo && (dtr >= 0)) {
	trmo=trmo.nextSibling;
	while (trmo && (trmo.nodeType != 1)) trmo = trmo.nextSibling; // case TEXT attribute in mozilla between TR
	dtr--;
      }
      if (trmo) {
	seltr=idro;
	movetr(trmo);
      }
    } else if (dtr < 0) {
      while (trmo && (dtr < -1)) {
	trmo=trmo.previousSibling;
	while (trmo && (trmo.nodeType != 1)) trmo = trmo.previousSibling; // case TEXT attribute in mozilla between TR
	dtr++;
      }
      if (trmo) {
	seltr=idro;
	movetr(trmo);
      }
    }
  }
  if (idro) idro.style.visibility='visible';
  dro=null;
  idro=null;
  draggo=false;
  delEvent(document,"mousemove",dragtr);   
  stopPropagation(event);

  
}


function dragtr(event) {  
  if (dro && draggo) {
    GetXY(event); 
    dro.style.top=Ypos-Math.round(hidro/2);
    //    dro.style.left=Xpos-10;
    // window.status='drag='+Ypos+'x'+Xpos;
  }
  return false;
}

document.write('<img id="WIMG" src="Images/loading.gif" style="display:none;position:absolute;background-color:#FFFFFF;border:groove black 2px;padding:4px;-moz-border-radius:4px">');
function viewwait(view) {
  var wimgo = document.getElementById('WIMG');
  if (wimgo) {
    if (view) {
      wimgo.style.display='inline';
      CenterDiv(wimgo.id);
    } else {
      wimgo.style.display='none';
    }
  }
}

function textautovsize(event,o) {
  if (! event) event=window.event;

  var i=1;
  var hb=o.clientHeight;
  var hs=o.scrollHeight;

  if (hs > hb) {
    o.parentNode.style.height=hs+'px';
    o.style.height=hs+'px';
  }
  
}

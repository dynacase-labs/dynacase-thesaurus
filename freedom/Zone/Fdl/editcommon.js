
var isNetscape = navigator.appName=="Netscape";
// auxilarry window to select choice
var wichoose= false;

var colorPick = new ColorPicker();
initDHTMLAPI();

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

function sendEnumChoice(event,docid,  choiceButton ,attrid, sorm) {


  var inp  = choiceButton.previousSibling;
  var index='';
  var attrid;
 
  var domindex=''; // needed to set values in arrays
  // search the input button in previous element
 
  var inid;


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


  

  f =document.modifydoc;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=FDL&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&index='+index+'&domindex='+domindex;

  

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
  disableReadAttribute();
  // reset to initial action
  f.action=oldact;
  f.target=oldtar;

}

function enableall() {

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) {	
           elements[i].disabled=false;
       }
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

    for (var i=0; i< attrNid.length; i++) {	
	if (document.getElementById(attrNid[i])) {
	  if (document.getElementById(attrNid[i]).value == '') {
	    ta = document.getElementsByName('_'+attrNid[i]+'[]');
	    if (ta.length == 0)	err += attrNid[i]+'\n';
	    for (var j=0; j< ta.length; j++) {
	      if (ta[j].value == '') err += attrNid[i]+'/'+(j+1)+'\n';
	    }
	  }
        }
    }
    if (err != '') {
	    alert(err+'[TEXT:some needed attributes are empty]');
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
    
    le = document.getElementsByName('_'+id+'[]');
    if ((le.length - 1) >= index) {
      return le[index].value;
    }    
  }
  return '';
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
	    document.getElementById(taout[c][i]).style.backgroundColor='[CORE_BGCOLORALTERN]';
	    if (inc)  inc.style.backgroundColor='[CORE_BGCOLORALTERN]';	      	    
	  } else {
	    
	    if (inc) inc.style.backgroundColor='';
	    if (document.getElementById(taout[c][i]).style.backgroundColor == '[CORE_BGCOLORALTERN]')
	      document.getElementById(taout[c][i]).style.backgroundColor == '';
	  }
	}
      } else {
	// search in arrays
	lin = document.getElementsByName('_'+taout[c][i]+'[]');

	for (var j=0; j< lin.length; j++) {
	  ndis=true;
	  for (var k=0; k< tain[c].length; k++) {
	    vin = getInputValue(tain[c][k],j);
	    if ((vin == '') || (vin == ' ')) ndis = false;
	    
	  }
	  if (lin[j].type != 'hidden') {
	    lin[j].disabled=ndis;
	    lin[j].style.backgroundColor=(ndis)?'[CORE_BGCOLORALTERN]':'';		
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
	document.getElementById(iinput).style.backgroundColor='[CORE_BGCOLORHIGH]';
	
      } else {
	err = err + "\n" + iinput;
      }
    } else {
      alert('[TEXT:Attribute not found]'+' : '+iinput);
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
  if (parseInt(docid) > 0) {
    if (! document.isSubmitted) {
      var fhidden = window.open('','fhidden','');
      fhidden.document.location.href='[CORE_STANDURL]&app=FDL&action=UNLOCKFILE&auto=Y&id='+docid;
    }
  }
}

function pleaseSave(event) {
  if (document.isChanged && (! document.isSubmitted) && (! document.isCancelled)) {
    if (confirm('[TEXT:Save changes ?]')) {
      var bsubmit= document.getElementById('iSubmit');

      var can=bsubmit.onclick.apply(null,[event]);

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

// change for time attribute
function chtime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);
  var shh,smm,ihh,imm;
  if (t && hh && mm) {
    ihh=parseInt(hh.value)%24;
    if (isNaN(ihh)) ihh=0;
    if (ihh < 10) shh='0'+ihh.toString();
    else shh=ihh.toString();
    hh.value=shh;

    imm=parseInt(mm.value)%60;
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
  var attnames = new Array('onclick','href','onmousedown','id','name');
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
		else if ((attr.name == 'onclick') || (attr.name == 'onmousedown')) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
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
 
  if (seltr)  {
    seltr.parentNode.insertBefore(ntr,seltr);
  } else {
    var ltr = ntable.getElementsByTagName('tr');
    var ltrfil=new Array();
    for (var i=0;i<ltr.length ;i++) {
      if ((ltr[i].parentNode.id == tbodyid) || (ltr[i].parentNode.parentNode.id == tbodyid)) ltrfil.push(ltr[i]);
    }
    if (ltrfil.length > 1) ltrfil[ltrfil.length-2].parentNode.insertBefore(ntr,ltrfil[ltrfil.length-2]);
  }
  
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
function delseltr() {


  if (seltr) {
    seltr.parentNode.removeChild(seltr);  
    seltr=false;
    visibilityinsert('insertup','hidden');
  }
  return;
  
}

function visibilityinsert(n,d) {
  var ti = document.getElementsByName(n);
  for (var i=0; i< ti.length; i++) { 
    ti[i].style.visibility=d;
  }
}

function selecttr(tr) {

  if (seltr) {
    seltr.style.backgroundColor='';
    
  } else {
    
    visibilityinsert('insertup','visible');
  }

  seltr=tr;

  seltr.style.backgroundColor='lightgrey';


  return;  
}

//unselect selected
function unseltr() {

  if (seltr) {
    seltr.style.backgroundColor='';
    
    visibilityinsert('insertup','hidden');
  }

  seltr=false;

  return;  
}

function movetr(tr) {

  var trnode= seltr;
  var pnode = tr;
  if (seltr) {  

    while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??
    if (pnode)  {
      trnode.parentNode.insertBefore(trnode,pnode);
    
    }  else {
      trnode.parentNode.appendChild(trnode); // latest (cyclic)
    }
    
    resetTrInputs(trnode);
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


//          ---------------------------------------------------------------------------------
// for idoc type documents 

function viewidoc_in_frame(idframe,xmlid,idocfam){
//cette meme fonction se trouve ds viewicard.js et editcard.js
//alert(idframe);
var iframe=document.getElementById(idframe);
iframe.style.display='inline';
//alert(iframe.name);
var xml_element = document.getElementById(xmlid);
var fxml = document.getElementById('fviewidoc');
fxml.xml.value=xml_element.value;
fxml.famid.value=idocfam;
//alert(iframe.id);
//alert(iframe.name);

fxml.target=iframe.name;
//alert(fxml.target);
//window.setTimeout("soumettre()",500);
fxml.submit();
}



function close_frame(idframe){
//cette meme fonction se trouve ds fdl_card.xml,viewicard.xml et editcard.js
var iframe=document.getElementById(idframe);
iframe.style.display='none';

}


function editidoc(idattr,xmlid,idocfam,attr_type) {

var xml_element = document.getElementById(xmlid);
var fxml = document.getElementById('fidoc');


 subwindowm(400,400,idattr,'[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT');    
exist=windowExist("un",true);
/*
if (!exist){
alert("exist pas");
}
if (exist){
alert("existe");
}
*/
fxml.famid.value=idocfam;
fxml.attrid.value=idattr;
fxml.type_attr.value=attr_type;

if(!exist){
fxml.xml.value="";
//subwindowm(400,400,idattr,'[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT');
fxml.action ="[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT";
fxml.target=idattr;

fxml.submit();
window.start=0;
}

fxml.xml.value=xml_element.value;
fxml.action ="[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT2";
fxml.target="un";

if(!exist){
temp=window.setInterval("wait(temp)",100);
}
else{
fxml.submit();
}

}


function wait(temp){

var fxml = document.getElementById('fidoc');
if (window.start==1){
//alert("ici");
fxml.submit();
window.clearInterval(temp);

}

}




///////for workflow familly dcocuments/////////////////////
function edit_transition(id){
var i=0;
var nom;
var noms_etats="";
var iddoc=id;

var tr =document.getElementById('tbodywor_etat');
liste=tr.getElementsByTagName("input");

for (var i=0;i<liste.length;i++){
hey=liste[i].id;
if (hey.indexOf("wor_nometat")==0){


	result=hey.split("wor_nometat");
	idetat=document.getElementById("wor_idetat"+result[1]);
	//alert(idetat.value);
	if (idetat.value!=""){
		noms_etats=noms_etats.concat(liste[i].value);
		noms_etats=noms_etats.concat(":"+idetat.value+",");
	}
	else{
	alert("utilisez l'aide a la saisie pour l'etat "+liste[i].value);
	}
}
}
//pour la derniere virgule en trop
noms_etats=noms_etats.substring(0,noms_etats.length-1);
//alert(noms_etats);



var typetrans=document.getElementById('listidoc_wor_tt');

valuestt="";
for (i=0;i<typetrans.length;i++){
	if (typetrans.options[i].value!=""){
	valuestt=valuestt.concat(typetrans.options[i].text);
	valuestt=valuestt.concat("*");
	valuestt=valuestt.concat(typetrans.options[i].id);
	//alert(typetrans.options[i].text);
	}
	else{
	alert("veuillez editer le nouveau type transition pour qu'il soit pris en compte");
	}

if ((i+1)!=typetrans.length){
valuestt=valuestt.concat(",");
}
}
//alert(valuestt);


subwindowm(600,600,'editransition',"[CORE_STANDURL]&app=FREEDOM&action=EDITRANSITION&docid="+iddoc+"&state="+noms_etats+"&tt="+valuestt);

}

function search_args(famid){
  //window.parent.document.getElementById("frameset").cols="50%,50%";
var id=document.getElementById("ai_idaction");
var attrid=document.getElementById("idattr").value;
var titre=document.getElementById("ba_title").value;
var nom=document.getElementById("ai_action").value;
//alert(xml_initial.value);
subwindowm(600,600,"deux","[CORE_STANDURL]&app=FREEDOM&action=RECUP_ARGS&docid="+id.value+"&titre="+titre+"&nom_act="+nom+"&attrid="+attrid+"&famid="+famid);

}


function view_second_frame(){
var  frameset =parent.document.getElementById("frameset");
frameset.cols="50%,50%";

//frameset.firstChild.nextSibling.noresize=0;
//document.getElementById("deux").noresize=0;
//parent.document.getElementById("deux").frameborder=1;
frameset.frameborder=1;

}




function doing(func,Args){

var tabArgs =Args.split(",");
//alert(tabArgs);
//alert(tabArgs.length);
try{

func.apply(null,tabArgs);
}
catch (e){
alert(e);
//alert("la fonction javascript associe a l'evenement  n'existe pas");
}

}


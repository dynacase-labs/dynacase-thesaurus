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


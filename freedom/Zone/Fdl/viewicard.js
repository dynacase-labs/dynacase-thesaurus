
function viewidoc_in_frame(idframe,xmlid,idocfam){
//cette meme fonction se trouve ds viewicard.js et editcard.js

var iframe=document.getElementById(idframe);
iframe.style.display='inline';
var xml_element = document.getElementById(xmlid);
var fxml = document.getElementById('fviewidoc');
fxml.xml.value=xml_element.value;
fxml.famid.value=idocfam;
fxml.target=idframe;
fxml.submit();

}



function close_frame(idframe){
//cette meme fonction se trouve ds fdl_card.xml,viewicard.xml et editcard.js
var iframe=document.getElementById(idframe);
iframe.style.display='none';
//iframe.visibility='hidden';
}

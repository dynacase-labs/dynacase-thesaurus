
var POPMENUINPROGRESSELT=false;
var POPMENUINPROGRESSEVENT=false;

function cloneEvent(e) {
  var c=new Object();
  var names="";
  for (var name in e) {
   try {
     c[name]=e[name];
   }
   catch (ex) {
     c[name]=false;
   }
  }
  return c;
}

function godocmenu(event,o) {
  if (window.event) {
    event=cloneEvent(window.event);  
  }
  POPMENUINPROGRESSELT = o;
  POPMENUINPROGRESSEVENT = event;
  //  displayPropertyNames(POPMENUINPROGRESSEVENT);
  setTimeout('setonclick()',200); // wait 200ms before send request for menu
}
function aborddocmenu(event) {
  POPMENUINPROGRESSELT = false;
}

function setonclick(event) {
  if (POPMENUINPROGRESSELT) {
    var o=POPMENUINPROGRESSELT;
    o.onclick.apply(o,[POPMENUINPROGRESSEVENT]);
    POPMENUINPROGRESSELT=false;
  }
}

function viewdocmenu(event,docid,onlyctrl,upobject,sourceobject) {
  if (!event) event=POPMENUINPROGRESSEVENT;

  POPMENUINPROGRESSELT=false;
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';
  var coord=false;  
  
  if (onlyctrl) menuopt='&onlyctrl=yes';
  else {
    if (ctrlPushed(event) && altPushed(event)) {
      menuapp='FDL';
      menuaction='POPUPDOCDETAIL';
    }     
  }
  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid;
  viewsubmenu(event,menuurl,upobject,sourceobject);
}


function viewdocsubmenu(event,docid,submenu,upobject) {
  POPMENUINPROGRESSELT=false;
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';
  var coord=false;
  if (submenu) menuopt='&submenu='+submenu;
  else {
    if (ctrlPushed(event) && altPushed(event)) {
      menuapp='FDL';
      menuaction='POPUPDOCDETAIL';
    }     
  }

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid;
  viewsubmenu(event,menuurl,upobject);
}

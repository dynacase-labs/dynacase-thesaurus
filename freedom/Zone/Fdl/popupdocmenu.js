
function viewdocmenu(event,docid,onlyctrl) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';

  if (ctrlPushed(event) && altPushed(event)) {
    menuapp='FDL';
    menuaction='POPUPDOCDETAIL';
  } else {
    if (onlyctrl) menuopt='&onlyctrl=yes';
  }
  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid;
  var source=false;
  viewmenu(event,menuurl,source);
}

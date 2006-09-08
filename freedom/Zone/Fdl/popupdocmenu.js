
function viewdocmenu(event,docid,source) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;

  if (ctrlPushed(event) && altPushed(event)) {
    menuapp='FDL';
    menuaction='POPUPDOCDETAIL';
  }
  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+'&id='+docid;

  viewmenu(event,menuurl,source);
}

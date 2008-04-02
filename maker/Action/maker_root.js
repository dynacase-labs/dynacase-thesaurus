include_js('FDL/Layout/popupdocmenu.js');
include_js('FDL/Layout/popupfunc.js');
function viewmakermenu(event,type,upobject) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp='MAKER';
  var menuaction='MAKER_MENU';
  var menuopt='&type='+type;

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt;
  viewsubmenu(event,menuurl,upobject);
}

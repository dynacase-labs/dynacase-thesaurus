
function viewdocmenu(event,docid,onlyctrl,upobject) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';
  var coord=false;
  if (ctrlPushed(event) && altPushed(event)) {
    menuapp='FDL';
    menuaction='POPUPDOCDETAIL';
  } else {
    if (onlyctrl) menuopt='&onlyctrl=yes';
  }
  if (upobject) {
    coord=new Object();;
    coord.x=AnchorPosition_getPageOffsetLeft(upobject);
    coord.y=AnchorPosition_getPageOffsetTop(upobject)+getObjectHeight(upobject);
  } 

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid;
  var source=false;
  viewmenu(event,menuurl,source,coord);
}

include_js('FDL/Layout/popupdocmenu.js');
include_js('FDL/Layout/popupfunc.js');
include_js('FDL/Layout/prototree.js');
include_js('FDL/Layout/iframe.js');
function viewmakermenu(event,type,upobject) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp='MAKER';
  var menuaction='MAKER_MENU';
  var menuopt='&type='+type;

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt;
  viewsubmenu(event,menuurl,upobject);
}


function viewprojecttree(event,where) {

  poptree(event,'[TEXT:the project tree]',10,20,300,500);
  poptest(event,'[TEXT:the test]',315,20,800,500);
  var CORE_STANDURL=window.location.pathname+'?sole=Y&';
  var url= CORE_STANDURL+'app=MAKER&action=MAKER_TREE&type=top';
  reloadtree(event,'toptree_c',url);
}
addEvent(window, 'load', viewprojecttree);



// create popup for insert div after
function poptree(event,divtitle,x,y,w,h) {
  var dpopdiv = document.getElementById('toptree_s');
  var fpopdiv;
  if (! dpopdiv) {
    new popUp(x, y, w, h, 'toptree', 'A', '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black', true, true, true, true, false, false,true);

    $('toptree_c').addClassName('prototree');
    
  } 
}

// create popup for insert div after
function poptest(event,divtitle,x,y,w,h) {
  var dpopdiv = document.getElementById('poptest_s');
  var fpopdiv;
  if (! dpopdiv) {
    new popUp(x, y, w, h, 'poptest', 'about:blank', '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black',true, true, true, true, true, false, true);

    
    
  } 
}

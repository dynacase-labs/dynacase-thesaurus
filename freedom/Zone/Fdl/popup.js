

function closeAllMenu() {
[BLOCK CMENUS]  closeMenu('[name]');
[ENDBLOCK CMENUS]
}
[BLOCK MENUS]
nbmitem['[name]'] =[nbmitem]; 
tdiv['[name]']= new Array([nbdiv]);
tdivid['[name]']=[menuitems];
[ENDBLOCK MENUS]

[BLOCK MENUACCESS]
tdiv['[name]'][[divid]]=[vmenuitems];
[ENDBLOCK MENUACCESS]




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


[BLOCK ADDMENUS]
nbmitem['[name]'] += [nbmitem]; 
tdivid['[name]']=tdivid['[name]'].concat([menuitems]);
[ENDBLOCK ADDMENUS]

[BLOCK ADDMENUACCESS]
tdiv['[name]'][[divid]]=tdiv['[name]'][[divid]].concat([vmenuitems]);
alert(tdivid['[name]'].toString());
[ENDBLOCK ADDMENUACCESS]


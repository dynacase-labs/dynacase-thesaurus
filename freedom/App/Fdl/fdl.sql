// recherche détaillée;fromid;;id;class;name;;;;;;;
;;;;;;;;;;;;
BEGIN;DSEARCH;rapport;25;;REPORT;;;;;;;
TYPE;C;;;;;;;;;;;
ICON;report.gif;;;;;;;;;;;
METHOD;Method.Report.php;;;;;;;;;;;
//;idattr;idframe;label;T;A;type;ord;vis;need;link;phpfile;phpfunc
ATTR;REP_FR_PRESENTATION;;Présentation;N;N;frame;0;F;;;;
ATTR;REP_SORT;REP_FR_PRESENTATION;tri;N;N;text;100;W;;;fdl.php;getSortAttr(D,SE_FAMID,REP_SORT):REP_IDSORT,REP_SORT
ATTR;REP_IDSORT;REP_FR_PRESENTATION;id tri;N;N;text;100;H;;;;
ATTR;REP_IDCOLS;REP_FR_PRESENTATION;id colonnes;N;N;textlist;100;I;;;;
ATTR;REP_EXCEL; ;vers tableur;N;N;menu;10;M;;%S%app=FDL&action=IMPCARD&props=N&id=%I%&ulink=N&ext=xls&mime=application/ms-excel;;
ATTR;REP_IMP; ;version imprimable;N;N;menu;10;M;;%S%app=FDL&action=IMPCARD&props=N&id=%I%&ulink=N;;
END;0;;;;;;;;;;;

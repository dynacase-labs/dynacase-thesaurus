vacuum full;
delete from doc where doctype='T';
delete from docattr where docid not in (select id from doc);
delete from fld where dirid not in (select id from doc);
delete from fld where childid not in (select id from doc) and qtype='S'; 
update doc set locked=0 where locked < -1;
vacuum full analyze;


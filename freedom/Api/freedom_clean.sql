vacuum full;
delete from doc where doctype='T';
delete from docvalue where docid not in (select id from doc);
delete from docvalue where attrid not in (select id from docattr);
delete from docattr where docid not in (select id from doc);
delete from fld where dirid not in (select id from doc);
delete from fld where childid not in (select id from doc) and qtype='S'; 
vacuum full analyze;


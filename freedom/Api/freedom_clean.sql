vacuum full;
delete from doc where doctype='T';
select setval('seq_id_tdoc', 1000000000);
delete from docattr where docid not in (select id from doc);
delete from fld where dirid not in (select id from doc);
delete from fld where childid not in (select id from doc) and qtype='S'; 
update doc set locked=0 where locked < -1;
update doc set postitid=0 where postitid > 0 and postitid not in (select id from doc27 where doctype != 'Z');
vacuum full analyze;

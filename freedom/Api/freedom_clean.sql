delete from only doc;
delete from docfrom;
insert INTO docfrom (id, fromid) select id, fromid from doc;
delete from docname;
insert INTO docname (name, id, fromid) select name,id, fromid from doc where name is not null and name != '' and locked != -1;
update docfrom set fromid=-1 where id in (select id from docfam);

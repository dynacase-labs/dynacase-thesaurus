

UPDATE docattr set needed='N';
UPDATE docattr set needed='Y' where visibility='N';
UPDATE docattr set visibility='W' where visibility='N';
UPDATE docattr set visibility='F' where type='frame';

update doc set usefor='P' where doctype='P';

create table docfamtemp (id int, cprofid int, dfldid int) ;
insert into docfamtemp (id, cprofid, dfldid) select id, cprofid, dfldid from only doc where  doctype='C';

create or replace function deletevalues() 
returns opaque as '
declare 
begin
delete from docvalue where docid=OLD.id;
return OLD;
end;
' language 'plpgsql';

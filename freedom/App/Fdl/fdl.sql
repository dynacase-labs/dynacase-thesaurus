create or replace function in_textlist(text, text) 
returns bool as '
declare 
  arg_tl alias for $1;
  arg_v alias for $2;
  rvalue bool;
  wt text;
begin
  rvalue := (arg_tl = arg_v) ;
  if (not rvalue) then	
    
     -- search in middle
    wt := \'\n\'||arg_v||\'\n\';
    rvalue := (position(wt in arg_tl) > 0);

     -- search in begin
     if (not rvalue) then	
       wt := arg_v||\'\n\';
       rvalue := (position(wt in arg_tl) = 1);

	
        -- search in end
       if (not rvalue) then	
          wt := \'\n\'||arg_v;
          rvalue := (position(wt in arg_tl) = (char_length(arg_tl)-char_length(arg_v))) and (position(wt in arg_tl) > 0);	
        end if;
     end if;
  end if;
  return rvalue;
end;
' language 'plpgsql';


-- str_replace r1 by r2 in s1
create or replace function str_replace(text, text, text) 
returns text as '
declare 
  s1 alias for $1;
  r1 alias for $2;
  r2 alias for $3;
  s2 text;
  sw text;
  p  int;
begin
  p := position(r1 in s1);
  sw := s1;

  while (p > 0) loop
    s2 := substring(sw FROM 0 FOR p);
    s2 := s2 || r2;
    p:= p+length(r1);
    s2 := s2 || substring(sw FROM p);

    -- try again
    p := position(r1 in s2);
    sw := s2;
  end loop;
  return sw;
end;
' language 'plpgsql';

-- change type of column
create or replace function alter_table_column(text, text, text) 
returns bool as '
declare 
  t alias for $1;
  col alias for $2;
  ctype alias for $3;
begin
   EXECUTE ''ALTER TABLE '' || quote_ident(t) || '' RENAME COLUMN   '' || col || '' TO zou'' || col;
   EXECUTE ''ALTER TABLE '' || quote_ident(t) || '' ADD COLUMN   '' || col || '' '' || ctype;	
   EXECUTE ''UPDATE '' || quote_ident(t) || '' set '' || col || ''='' || ''zou'' || col|| ''::'' || ctype;
   EXECUTE ''ALTER TABLE '' || quote_ident(t) || '' DROP COLUMN   zou'' || col ;		
 
   return true;
end;
' language 'plpgsql';

create or replace function flog(int, int) 
returns bool as '
declare 
  tlog int;
begin

   select into tlog t from log ;
    if (tlog is null) then
      tlog:=0;
      insert into log (t) values (0); 
   end if;
   tlog := tlog+1;
   update log set t=tlog;
return true;


end;
' language 'plpgsql' with (iscachable);

create or replace function computegperm(int, int) 
returns int as '
declare 
  a_userid alias for $1;
  a_profid alias for $2;
  uperm int;
  xgroup RECORD;
  gperm int;
  
begin
   if (a_userid = 1) or (a_profid <= 0) then 
     return -1; -- it is admin user or no controlled object
   end if;
  
   uperm := 0;
   for xgroup in select idgroup from groups where iduser=a_userid loop
     gperm := getuperm(xgroup.idgroup, a_profid);
  
     uperm := gperm | uperm;
    
   end loop;


   return uperm;
end;
' language 'plpgsql' with (iscachable);



create or replace function getuperm(int, int) 
returns int as '
declare 
  a_userid alias for $1;
  a_profid alias for $2;
  uperm int;
  gperm int;
  upperm int;
  unperm int;
  tlog int;
begin
   if (a_userid = 1) or (a_profid <= 0) then 
     return -1; -- it is admin user or no controlled object
   end if;
  
   select into uperm, upperm, unperm cacl, upacl, unacl from docperm where docid=a_profid and userid=a_userid;

   if (uperm is null) then
     uperm := computegperm(a_userid,a_profid);
     uperm := uperm | 1;
     insert into docperm (docid, userid, upacl, unacl, cacl) values (a_profid,a_userid,0,0,uperm); 
     return uperm;
   end if;

   if (uperm = 0) then
     gperm := computegperm(a_userid,a_profid);
    
     uperm := ((gperm | upperm) & (~ unperm)) | 1;

     update docperm set cacl=uperm where docid=a_profid and userid=a_userid;
   end if;

   return uperm;
end;
' language 'plpgsql' with (iscachable);

create or replace function hasviewprivilege(int, int) 
returns bool as '
declare 
  a_userid alias for $1;
  a_profid alias for $2;
  uperm int;
begin
   
   uperm := getuperm(a_userid, a_profid);


   return ((uperm & 2) != 0);
end;
' language 'plpgsql' with (iscachable);


create or replace function hasdocprivilege(int, int, int) 
returns bool as '
declare 
  a_userid alias for $1;
  a_profid alias for $2;
  a_pos alias for $3;
  uperm int;
begin
   
   uperm := getuperm(a_userid, a_profid);


   return ((uperm & a_pos) != 0);
end;
' language 'plpgsql' with (iscachable);


create or replace function getdocvalues(int) 
returns varchar as '
declare 
  arg_doc alias for $1;
  rvalue docvalue%ROWTYPE;
  values text;
begin
values := '''';
for rvalue in select  * from docvalue where  (docid=arg_doc)  loop
	values := values || ''['' || rvalue.attrid || '';;'' || rvalue.value || '']'';
end loop;
return values;
end;
' language 'plpgsql';





create or replace function deletevalues() 
returns trigger as '
declare 
begin
--delete from docvalue where docid=OLD.id;
--delete from docperm where docid=OLD.id;
return OLD;
end;
' language 'plpgsql';


create or replace function resetvalues() 
returns trigger as '
declare 
begin
NEW.values:='''';
NEW.attrids:='''';
return NEW;
end;
' language 'plpgsql';

create or replace function initacl() 
returns trigger as '
declare 
begin
if (TG_OP = ''UPDATE'') then
   if (NEW.cacl != 0)  and ((NEW.upacl != OLD.upacl) OR (NEW.unacl != OLD.unacl)) then
     update docperm set cacl=0 where docid=NEW.docid;
   end if;
end if;

if (TG_OP = ''INSERT'') then
   if (NEW.cacl != 0) then 
     update docperm set cacl=0 where docid=NEW.docid;
   end if;
end if;
return NEW;
end;
' language 'plpgsql';



create or replace function fixeddoc() 
returns trigger as '
declare 
   lid int;
   cfromid int;
begin


if (TG_OP = ''INSERT'') then
     if (NEW.doctype = ''C'') then 
       cfromid=-1; -- for families
     else
       cfromid=NEW.fromid;
       if (NEW.revision > 0) then
         EXECUTE ''update doc'' || cfromid || '' set lmodify=\\''N\\'' where initid= '' || NEW.initid;
         EXECUTE ''update doc'' || cfromid || '' set lmodify=\\''L\\'' where  id=(select distinct on (initid) id from doc where initid = '' || NEW.initid || '' and locked = -1 order by initid, revision desc)'';
       end if;
     end if;
     select into lid id from docfrom where id= NEW.id;
     if (lid = NEW.id) then 
	update docfrom set fromid=cfromid where id=NEW.id;
     else 
	insert into docfrom (id,fromid) values (NEW.id, cfromid);
     end if;
end if;
 
return NEW;
end;
' language 'plpgsql';

create or replace function droptrigger(name) 
returns bool as '
declare 
  tname alias for $1;
  toid oid;
  trigname pg_trigger%ROWTYPE;
begin
   select into toid oid from pg_class where relname=tname;
   --select into trigname tgname from pg_trigger where tgrelid=toid;
   for trigname in select * from pg_trigger where tgrelid=toid  loop
--	 drop trigger quote_ident(trigname.tgname) on tname;
         EXECUTE ''DROP TRIGGER '' || quote_ident(trigname.tgname) || '' on  '' || tname;
   end loop;



   return true;
end;
' language 'plpgsql' ;




create or replace function disabledtrigger(name) 
returns bool as '
declare 
  tname alias for $1;
begin
   EXECUTE ''UPDATE pg_catalog.pg_class SET reltriggers = 0 WHERE oid = \\'''' || quote_ident(tname) || ''\\''::pg_catalog.regclass'';



   return true;
end;
' language 'plpgsql' ;




create or replace function enabledtrigger(name) 
returns bool as '
declare 
  tname alias for $1;
begin
   EXECUTE ''UPDATE pg_catalog.pg_class SET reltriggers = (SELECT pg_catalog.count(*) FROM pg_catalog.pg_trigger where pg_class.oid = tgrelid) WHERE oid =  \\'''' || quote_ident(tname) || ''\\''::pg_catalog.regclass;'';



   return true;
end;
' language 'plpgsql' ;



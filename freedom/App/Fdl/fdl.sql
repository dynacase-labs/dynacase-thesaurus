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
    wt := \'\n\'||arg_v||\'\r\';
    rvalue := (position(wt in arg_tl) > 0);

     -- search in begin
     if (not rvalue) then	
       wt := arg_v||\'\r\';
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

create or replace function computegperm(int, int) 
returns int as '
declare 
  a_userid alias for $1;
  profid alias for $2;
  uperm int;
  group RECORD;
  gperm int;
  
begin
   if (a_userid = 1) or (profid <= 0) then 
     return -1; -- it is admin user or no controlled object
   end if;
  
   uperm := 0;
   for group in select idgroup from groups where iduser=a_userid loop
     gperm := getuperm(group.idgroup, profid);
  
     uperm := gperm | uperm;
    
   end loop;


   return uperm;
end;
' language 'plpgsql' with (iscachable);


create or replace function getuperm(int, int) 
returns int as '
declare 
  a_userid alias for $1;
  profid alias for $2;
  uperm int;
  gperm int;
  upperm int;
  unperm int;
begin
   if (a_userid = 1) or (profid <= 0) then 
     return -1; -- it is admin user or no controlled object
   end if;
  
   select into uperm, upperm, unperm cacl, upacl, unacl from docperm where docid=profid and userid=a_userid;

   if (uperm is null) then
     uperm := computegperm(a_userid,profid);
     insert into docperm (docid, userid, upacl, unacl, cacl) values (profid,a_userid,0,0,uperm); 
     return uperm;
   end if;

   if (uperm = 0) then
     gperm := computegperm(a_userid,profid);
    
     uperm := ((gperm | upperm) & (~ unperm)) | 1;

     update docperm set cacl=uperm where docid=profid and userid=a_userid;
   end if;

--   select into unperm cacl from docperm where docid=profid and userid=0;
--	 if (unperm is null) then
--    unperm:=0;
--    insert into docperm (docid, userid, upacl, unacl, cacl) values (profid,0,0,0,0); 
--   end if;
--   unperm := unperm+1;
--   update docperm set cacl=unperm where docid=profid and userid=0;
   return uperm;
end;
' language 'plpgsql' with (iscachable);

create or replace function hasviewprivilege(int, int) 
returns bool as '
declare 
  a_userid alias for $1;
  profid alias for $2;
  uperm int;
begin
   
   uperm := getuperm(a_userid, profid);


   return ((uperm & 2) != 0);
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
returns opaque as '
declare 
begin
delete from docvalue where docid=OLD.id;
delete from docperm where docid=OLD.id;
return OLD;
end;
' language 'plpgsql';


create or replace function resetvalues() 
returns opaque as '
declare 
begin
NEW.values:='''';
NEW.attrids:='''';
return NEW;
end;
' language 'plpgsql';

create or replace function initacl() 
returns opaque as '
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
